<?php

namespace App\Controllers\Api;

use App\Models\TimeSlotModel;
use App\Models\CustomerModel;
use App\Models\BookingModel;

class BookingController extends BaseApiController
{
    public function book(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data = $this->request->getJSON(true);

        // Validate
        $rules = [
            'slot_id' => 'required|integer',
            'name'    => 'required|min_length[2]',
            'email'   => 'required|valid_email',
            'phone'   => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Dados inválidos.', 422, $this->validator->getErrors());
        }

        $slotModel = new TimeSlotModel();
        $slot = $slotModel->find($data['slot_id']);

        if ($slot === null || $slot['status'] !== 'available') {
            return $this->error('Este horário não está disponível.', 409);
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->findOrCreate([
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $bookingModel = new BookingModel();
        $bookingModel->insert([
            'time_slot_id' => $slot['id'],
            'customer_id'  => $customer['id'],
            'notes'        => $data['notes'] ?? null,
            'status'       => 'confirmed',
            'booked_at'    => date('Y-m-d H:i:s'),
        ]);

        $slotModel->update($slot['id'], ['status' => 'booked']);

        return $this->success(['booking_id' => $bookingModel->getInsertID()], 'Agendamento confirmado!', 201);
    }

    public function myBookings(): \CodeIgniter\HTTP\ResponseInterface
    {
        $customerId = $this->request->customer['id'];
        $bookings   = (new BookingModel())->getByCustomer($customerId);

        return $this->success($bookings);
    }

    public function cancel(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $customerId   = $this->request->customer['id'];
        $bookingModel = new BookingModel();

        if (! $bookingModel->cancelBooking($id, $customerId)) {
            return $this->error('Não foi possível cancelar este agendamento.', 400);
        }

        return $this->success(null, 'Agendamento cancelado.');
    }

    // Admin API methods
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $bookings = (new BookingModel())->getWithDetails($this->request->getGet('status'));
        return $this->success($bookings);
    }

    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $data   = $this->request->getJSON(true);
        $model  = new BookingModel();
        $model->update($id, ['status' => $data['status'] ?? 'confirmed']);

        return $this->success(null, 'Atualizado.');
    }
}
