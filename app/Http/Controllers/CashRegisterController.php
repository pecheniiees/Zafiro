<?php

namespace App\Http\Controllers;

use App\Actions\CreateDashboardForUserAction;
use App\Http\Requests\OpenCashShiftRequest;
use App\Http\Requests\StoreCashTransactionRequest;
use App\Http\Requests\StorePosSaleRequest;
use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\Dashboard;
use App\Services\CashRegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CashRegisterController extends Controller
{
    public function __construct(
        private CashRegisterService $cash,
        private CreateDashboardForUserAction $createDashboard,
    ) {}

    public function index(Request $request): View
    {
        $dashboard = $this->dashboard($request);
        $shift = $dashboard->cashShifts()
            ->where('status', CashShift::STATUS_OPEN)
            ->with(['transactions' => fn ($query) => $query->latest()])
            ->first();
        $products = $dashboard->products()
            ->select(['id', 'name', 'category', 'price', 'quantity'])
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return view('cash-register.index', [
            'shift' => $shift,
            'totals' => $shift ? $this->cash->totals($shift) : null,
            'products' => $products,
        ]);
    }

    public function open(OpenCashShiftRequest $request): RedirectResponse
    {
        $this->cash->open($this->dashboard($request), (float) $request->validated('opening_balance'), $request->user()?->getKey());

        return back()->with('status', 'Смена открыта');
    }

    public function transaction(StoreCashTransactionRequest $request): RedirectResponse
    {
        $this->cash->transact($this->dashboard($request), $request->validated(), $request->user()?->getKey());

        return back()->with('status', 'Операция проведена');
    }

    public function sale(StorePosSaleRequest $request): RedirectResponse
    {
        $transaction = $this->cash->sale($this->dashboard($request), $request->validated('items'), $request->user()?->getKey());

        return redirect()->route('cash-register.receipt', $transaction)
            ->with('status', 'Продажа проведена. Распечатайте чек.');
    }

    public function receipt(Request $request, CashTransaction $cashTransaction): View
    {
        $this->authorizeTransaction($this->dashboard($request), $cashTransaction);
        abort_unless($cashTransaction->type === CashTransaction::TYPE_INCOME, 404);

        return view('cash-register.receipt', [
            'transaction' => $cashTransaction->load('shift'),
        ]);
    }

    public function xReport(Request $request): View
    {
        $dashboard = $this->dashboard($request);
        $shift = $dashboard->cashShifts()->where('status', CashShift::STATUS_OPEN)->firstOrFail();

        return view('cash-register.report', ['shift' => $shift, 'totals' => $this->cash->totals($shift), 'reportType' => 'X']);
    }

    public function close(Request $request, CashShift $cashShift): RedirectResponse
    {
        $dashboard = $this->dashboard($request);
        $this->authorizeShift($dashboard, $cashShift);

        if (! $request->session()->pull("z_report_printed.{$cashShift->id}", false)) {
            return redirect()->route('cash-register.z-report', $cashShift)
                ->withErrors(['shift' => 'Сначала распечатайте Z-отчёт. Без печати смена не закрывается.']);
        }

        $shift = $this->cash->close($dashboard, $cashShift, $request->user()?->getKey());

        return redirect()->route('cash-register.z-report', $shift);
    }

    public function zReport(Request $request, CashShift $cashShift): View
    {
        $this->authorizeShift($this->dashboard($request), $cashShift);

        $nonce = Str::random(40);
        $request->session()->put("z_report_nonce.{$cashShift->id}", $nonce);

        return view('cash-register.report', [
            'shift' => $cashShift,
            'totals' => $this->cash->totals($cashShift),
            'reportType' => 'Z',
            'printNonce' => $nonce,
        ]);
    }

    public function markZReportPrinted(Request $request, CashShift $cashShift): JsonResponse
    {
        $this->authorizeShift($this->dashboard($request), $cashShift);
        abort_unless($cashShift->status === CashShift::STATUS_OPEN, 422);

        $nonce = (string) $request->input('nonce');
        $expected = (string) $request->session()->pull("z_report_nonce.{$cashShift->id}");
        abort_unless($expected !== '' && hash_equals($expected, $nonce), 419);

        $request->session()->put("z_report_printed.{$cashShift->id}", true);

        return response()->json(['printed' => true]);
    }

    private function dashboard(Request $request): Dashboard
    {
        return $request->user()->dashboard ?? $this->createDashboard->handle($request->user());
    }

    private function authorizeShift(Dashboard $dashboard, CashShift $cashShift): void
    {
        abort_unless((int) $cashShift->dashboard_id === $dashboard->id, 404);
    }

    private function authorizeTransaction(Dashboard $dashboard, CashTransaction $cashTransaction): void
    {
        abort_unless((int) $cashTransaction->dashboard_id === $dashboard->id, 404);
    }
}
