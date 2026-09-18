<?php

namespace App\Http\Controllers;

use App\Actions\CreateDashboardForUserAction;
use App\Http\Requests\StoreClubMemberRequest;
use App\Models\Dashboard;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClubMemberController extends Controller
{
    public function __construct(private CreateDashboardForUserAction $createDashboard) {}

    public function index(Request $request): View
    {
        $dashboard = $this->dashboard($request);

        $members = $dashboard->clubMembers()
            ->select(['id', 'dashboard_id', 'user_id', 'balance', 'bonus_balance', 'status', 'created_at'])
            ->with('user:id,name,phone,email')
            ->latest()
            ->get();

        return view('club-members.index', [
            'members' => $members,
            'statusNames' => [
                'active' => 'Активен',
                'blocked' => 'Заблокирован',
                'inactive' => 'Неактивен',
            ],
        ]);
    }


    public function store(StoreClubMemberRequest $request): RedirectResponse
    {
        $dashboard = $this->dashboard($request);
        $data = $request->validated();

        DB::transaction(function () use ($dashboard, $data): void {
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            $dashboard->clubMembers()->create([
                'user_id' => $user->id,
                'balance' => $data['balance'] ?? 0,
                'bonus_balance' => $data['bonus_balance'] ?? 0,
                'status' => $data['status'],
            ]);
        });

        return redirect()->route('club-members.index')->with('status', 'Пользователь создан.');
    }

    private function dashboard(Request $request): Dashboard
    {
        return $request->user()->dashboard ?? $this->createDashboard->handle($request->user());
    }
}
