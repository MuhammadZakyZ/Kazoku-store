<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait ChecksOrderAccess
{
    /** Memakai OrderPolicy::view (pemilik atau admin); selain itu 404. */
    protected function authorizeOrder(Request $request, Order $order): void
    {
        Gate::authorize('view', $order);
    }
}