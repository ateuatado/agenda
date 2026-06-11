<?php

namespace App\Controllers\Api;

use App\Models\CustomerModel;
use App\Models\InterestModel;
use App\Models\TimeSlotModel;

class InterestController extends BaseApiController
{
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data  = $this->request->getJSON(true);
        $rules = [
            'slot_id' => 'required|integer',
            'name'    => 'required',
            'email'   => 'required|valid_email',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Dados inválidos.', 422, $this->validator->getErrors());
        }

        $slotModel = new TimeSlotModel();
        $slot = $slotModel->find($data['slot_id']);

        if ($slot === null) {
            return $this->error('Slot não encontrado.', 404);
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->findOrCreate([
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        $interestModel = new InterestModel();
        if (! $interestModel->hasInterest($data['slot_id'], $customer['id'])) {
            $interestModel->insert([
                'time_slot_id' => $data['slot_id'],
                'customer_id'  => $customer['id'],
            ]);
        }

        return $this->success(null, 'Interesse registrado!', 201);
    }

    public function myInterests(): \CodeIgniter\HTTP\ResponseInterface
    {
        $customerId = $this->request->customer['id'];
        $interests  = (new InterestModel())->getByCustomer($customerId);

        return $this->success($interests);
    }
}
