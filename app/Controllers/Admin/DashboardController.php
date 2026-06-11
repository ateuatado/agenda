<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\BookingModel;
use App\Models\TimeSlotModel;
use App\Models\CustomerModel;

class DashboardController extends BaseAdminController
{
    public function index(): string
    {
        $slotModel    = new TimeSlotModel();
        $bookingModel = new BookingModel();
        $customerModel = new CustomerModel();

        $year  = (int) date('Y');
        $month = (int) date('m');

        $data = [
            'title'             => 'Dashboard',
            'slotCounts'        => $slotModel->countByStatus($year, $month),
            'upcomingBookings'  => $bookingModel->countUpcoming(),
            'totalCustomers'    => $customerModel->countAll(),
            'recentBookings'    => $bookingModel->getWithDetails('confirmed'),
            'currentMonth'      => date('F Y'),
        ];

        return view('admin/dashboard/index', $data);
    }
}
