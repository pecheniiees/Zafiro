<?php

namespace App\Http\Controllers;

use App\Actions\CreateDashboardForUserAction;
use App\Models\CashTransaction;
use App\Models\ClubComputer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, CreateDashboardForUserAction $createDashboard): View
    {
        /** @var User $user */
        $user = $request->user();
        $dashboard = $user->dashboard ?? $createDashboard->handle($user);
        $today = now()->startOfDay();
        $month = now()->startOfMonth();

        $todayIncome = (float) $dashboard->cashTransactions()
            ->where('type', CashTransaction::TYPE_INCOME)
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $monthIncome = (float) $dashboard->cashTransactions()
            ->where('type', CashTransaction::TYPE_INCOME)
            ->where('created_at', '>=', $month)
            ->sum('amount');

        $monthExpense = (float) $dashboard->cashTransactions()
            ->where('type', CashTransaction::TYPE_EXPENSE)
            ->where('created_at', '>=', $month)
            ->sum('amount');

        $clubMembers = $dashboard->clubMembers();
        $products = $dashboard->products();
        $zones = $dashboard->clubZones();

        $computerStatusCounts = ClubComputer::query()
            ->whereHas('zone', fn ($query) => $query->where('dashboard_id', $dashboard->id))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $chartLabels = collect(range(6, 0))->map(fn (int $daysAgo): string => now()->subDays($daysAgo)->format('d.m'));
        $incomeByDate = $dashboard->cashTransactions()
            ->where('type', CashTransaction::TYPE_INCOME)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['amount', 'created_at'])
            ->groupBy(fn (CashTransaction $transaction): string => $transaction->created_at->format('Y-m-d'));
        $expenseByDate = $dashboard->cashTransactions()
            ->where('type', CashTransaction::TYPE_EXPENSE)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['amount', 'created_at'])
            ->groupBy(fn (CashTransaction $transaction): string => $transaction->created_at->format('Y-m-d'));

        $chartIncome = collect(range(6, 0))->map(function (int $daysAgo) use ($incomeByDate): float {
            $key = now()->subDays($daysAgo)->format('Y-m-d');

            return (float) ($incomeByDate->get($key)?->sum(fn (CashTransaction $transaction): float => (float) $transaction->amount) ?? 0);
        });
        $chartExpense = collect(range(6, 0))->map(function (int $daysAgo) use ($expenseByDate): float {
            $key = now()->subDays($daysAgo)->format('Y-m-d');

            return (float) ($expenseByDate->get($key)?->sum(fn (CashTransaction $transaction): float => (float) $transaction->amount) ?? 0);
        });

        return view('dashboard', [
            'dashboard' => $dashboard,
            'stats' => [
                'today_income' => $todayIncome,
                'month_income' => $monthIncome,
                'month_expense' => $monthExpense,
                'month_net' => $monthIncome - $monthExpense,
                'club_members' => (clone $clubMembers)->count(),
                'active_members' => (clone $clubMembers)->where('status', 'active')->count(),
                'member_balance' => (float) (clone $clubMembers)->sum('balance'),
                'member_bonus_balance' => (float) (clone $clubMembers)->sum('bonus_balance'),
                'products' => (clone $products)->count(),
                'low_stock_products' => (clone $products)->where('quantity', '<=', 10)->count(),
                'zones' => (clone $zones)->count(),
                'computers' => (int) $computerStatusCounts->sum(),
                'computers_on' => (int) ($computerStatusCounts[ClubComputer::STATUS_ON] ?? 0),
                'computers_reserved' => (int) ($computerStatusCounts[ClubComputer::STATUS_RESERVED] ?? 0),
                'computers_maintenance' => (int) ($computerStatusCounts[ClubComputer::STATUS_MAINTENANCE] ?? 0),
            ],
            'recentTransactions' => $dashboard->cashTransactions()
                ->select(['id', 'type', 'amount', 'description', 'created_at'])
                ->latest()
                ->limit(5)
                ->get(),
            'recentMovements' => $dashboard->stockMovements()
                ->select(['id', 'product_id', 'type', 'quantity', 'balance_after', 'created_at'])
                ->with('product:id,name')
                ->latest()
                ->limit(5)
                ->get(),
            'lowStockProducts' => $dashboard->products()
                ->select(['id', 'name', 'quantity'])
                ->where('quantity', '<=', 10)
                ->orderBy('quantity')
                ->limit(5)
                ->get(),
            'chart' => [
                'labels' => $chartLabels->all(),
                'income' => $chartIncome->all(),
                'expense' => $chartExpense->all(),
            ],
        ]);
    }
}
