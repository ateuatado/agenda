<?php

namespace App\Controllers\Admin;

use App\Models\BookingModel;

class BookingController extends BaseAdminController
{
    protected BookingModel $model;

    public function __construct()
    {
        $this->model = new BookingModel();
    }

    public function index(): string
    {
        $status   = $this->request->getGet('status');
        $bookings = $this->model->getWithDetails($status);

        return view('admin/bookings/index', [
            'title'    => 'Agendamentos',
            'bookings' => $bookings,
            'status'   => $status,
        ]);
    }

    public function show(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $booking = $this->model->getWithDetails()[0] ?? null;
        // Get specific booking with details
        $bookings = $this->model->getWithDetails();
        $booking  = null;
        foreach ($bookings as $b) {
            if ((int) $b['id'] === $id) {
                $booking = $b;
                break;
            }
        }

        if ($booking === null) {
            return redirect()->to(route_to('admin.bookings'))->with('error', 'Agendamento não encontrado.');
        }

        return view('admin/bookings/show', [
            'title'   => 'Detalhes do Agendamento',
            'booking' => $booking,
        ]);
    }

    public function updateStatus(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $status = $this->request->getPost('status');
        $allowed = ['pending', 'confirmed', 'cancelled'];

        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', 'Status inválido.');
        }

        $this->model->update($id, ['status' => $status]);

        return redirect()->back()->with('success', 'Status atualizado!');
    }
}
