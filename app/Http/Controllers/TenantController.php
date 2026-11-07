<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Tenants/Index', [
            'tenants' => $request->user()->tenants()->with('room:id,tenant_id,room_number')->orderBy('name')->paginate(10),
            'roomCount' => $request->user()->rooms()->count(),
            'overdueCount' => $request->user()->rooms()->where('status', 'occupied')->whereDate('next_rent_due_date', '<', now()->toDateString())->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->tenants()->create($this->validated($request));

        return to_route('tenants.index')->with('success', 'Tenant added.');
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant = $this->ownedTenant($request, $tenant);
        $tenant->update($this->validated($request));

        return to_route('tenants.index')->with('success', 'Tenant updated.');
    }

    public function destroy(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant = $this->ownedTenant($request, $tenant);
        abort_if($tenant->room()->exists(), 422, 'Move the tenant out of their room before deleting them.');
        $tenant->delete();

        return to_route('tenants.index')->with('success', 'Tenant deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);
    }

    private function ownedTenant(Request $request, Tenant $tenant): Tenant
    {
        abort_unless($tenant->user_id === $request->user()->id, 404);

        return $tenant;
    }
}
