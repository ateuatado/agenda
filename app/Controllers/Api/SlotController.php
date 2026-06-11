<?php

namespace App\Controllers\Api;

use App\Models\TimeSlotModel;
use App\Models\SessionTypeModel;

class SlotController extends BaseApiController
{
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('m'));
        $slots = (new TimeSlotModel())->getByMonth($year, $month);

        return $this->success($slots);
    }

    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data        = $this->request->getJSON(true);
        $slotModel   = new TimeSlotModel();
        $sessionType = (new SessionTypeModel())->find($data['session_type_id'] ?? 0);

        if ($sessionType === null) {
            return $this->error('Tipo de sessão não encontrado.', 404);
        }

        $data['end_time'] = date('H:i:s', strtotime($data['start_time']) + $sessionType['duration_minutes'] * 60);
        $data['status']   = $data['status'] ?? 'available';

        $slotModel->insert($data);

        return $this->success(['id' => $slotModel->getInsertID()], 'Slot criado.', 201);
    }

    public function batch(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data = $this->request->getJSON(true);
        // Batch logic via API (same as web controller)
        return $this->success(null, 'Use o painel admin para criação em lote.', 200);
    }

    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $data      = $this->request->getJSON(true);
        $slotModel = new TimeSlotModel();
        $slot      = $slotModel->find($id);

        if ($slot === null) {
            return $this->error('Slot não encontrado.', 404);
        }

        $slotModel->update($id, $data);

        return $this->success(null, 'Slot atualizado.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $slotModel = new TimeSlotModel();
        $slot = $slotModel->find($id);

        if ($slot === null) {
            return $this->error('Slot não encontrado.', 404);
        }

        $slotModel->delete($id);

        return $this->success(null, 'Slot removido.');
    }
}
