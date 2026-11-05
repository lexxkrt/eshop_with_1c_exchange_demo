<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

/**
 * Панель администратора.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->withCount('items')
            ->latest()
            ->paginate(20);

        return view('admin.dashboard', [
            'orders' => $orders,
            'ordersTotal' => Order::count(),
            'ordersNew' => Order::where('status', 'new')->count(),
        ]);
    }
}
