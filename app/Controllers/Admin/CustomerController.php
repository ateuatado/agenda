<?php

namespace App\Controllers\Admin;

use App\Models\CustomerModel;
use App\Models\BookingModel;

class CustomerController extends BaseAdminController
{
    public function index(): string
    {
        $customerModel = new CustomerModel();

        return view('admin/customers/index', [
            'title'     => 'Clientes',
            'customers' => $customerModel->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function show(int $id): string
    {
        $customerModel = new CustomerModel();
        $bookingModel  = new BookingModel();

        $customer = $customerModel->findOrFail($id);
        $bookings = $bookingModel->getByCustomer($id);

        return view('admin/customers/show', [
            'title'    => 'Cliente: ' . $customer['name'],
            'customer' => $customer,
            'bookings' => $bookings,
        ]);
    }
}
