<?php

namespace App\Controllers\Admin;

use App\Models\TimeSlotModel;
use App\Models\SessionTypeModel;

class SlotController extends BaseAdminController
{
    protected TimeSlotModel $model;

    public function __construct()
    {
        $this->model = new TimeSlotModel();
    }

    public function index(): string
    {
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('m'));

        return view('admin/slots/index', [
            'title'        => 'Agenda — Slots',
            'slots'        => $this->model->getByMonth($year, $month),
            'sessionTypes' => (new SessionTypeModel())->getActive(),
            'year'         => $year,
            'month'        => $month,
        ]);
    }

    public function new(): string
    {
        return view('admin/slots/form', [
            'title'        => 'Novo Slot',
            'slot'         => null,
            'sessionTypes' => (new SessionTypeModel())->getActive(),
        ]);
    }

    public function create(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->request->getPost(['session_type_id', 'date', 'start_time', 'notes', 'status']);
        $data['status'] = $data['status'] ?? 'available';

        // Calculate end_time from session type duration
        $sessionType = (new SessionTypeModel())->find($data['session_type_id']);
        if ($sessionType) {
            $data['end_time'] = date('H:i:s', strtotime($data['start_time']) + $sessionType['duration_minutes'] * 60);
        }

        if (! $this->model->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(route_to('admin.slots'))->with('success', 'Slot criado com sucesso!');
    }

    public function batch(): string
    {
        return view('admin/slots/batch', [
            'title'        => 'Criar Slots em Lote',
            'sessionTypes' => (new SessionTypeModel())->getActive(),
        ]);
    }

    public function storeBatch(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post           = $this->request->getPost();
        $sessionTypeId  = (int) $post['session_type_id'];
        $startDate      = $post['start_date'];
        $endDate        = $post['end_date'];
        $daysOfWeek     = $post['days_of_week'] ?? [];
        $times          = array_filter(explode("\n", str_replace("\r", '', $post['times'] ?? '')));
        $status         = $post['status'] ?? 'available';

        $sessionType = (new SessionTypeModel())->find($sessionTypeId);
        if (! $sessionType) {
            return redirect()->back()->with('error', 'Tipo de sessão inválido.');
        }

        $slots   = [];
        $now     = date('Y-m-d H:i:s');
        $current = strtotime($startDate);
        $end     = strtotime($endDate);

        while ($current <= $end) {
            $dayOfWeek = (int) date('w', $current); // 0=Sun, 6=Sat
            if (in_array((string) $dayOfWeek, $daysOfWeek, true)) {
                foreach ($times as $time) {
                    $time = trim($time);
                    if (! $time) continue;
                    $slots[] = [
                        'session_type_id' => $sessionTypeId,
                        'date'            => date('Y-m-d', $current),
                        'start_time'      => $time,
                        'end_time'        => date('H:i:s', strtotime($time) + $sessionType['duration_minutes'] * 60),
                        'status'          => $status,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }
            }
            $current = strtotime('+1 day', $current);
        }

        if (empty($slots)) {
            return redirect()->back()->with('error', 'Nenhum slot gerado. Verifique os parâmetros.');
        }

        $this->model->createBatch($slots);
        $count = count($slots);

        return redirect()->to(route_to('admin.slots'))->with('success', "{$count} slots criados com sucesso!");
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $slot = $this->model->find($id);
        if (! $slot) {
            return redirect()->to(route_to('admin.slots'))->with('error', 'Slot não encontrado.');
        }

        return view('admin/slots/form', [
            'title'        => 'Editar Slot',
            'slot'         => $slot,
            'sessionTypes' => (new SessionTypeModel())->getActive(),
        ]);
    }

    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->model->find($id)) {
            return redirect()->to(route_to('admin.slots'))->with('error', 'Slot não encontrado.');
        }
        $data = $this->request->getPost(['session_type_id', 'date', 'start_time', 'notes', 'status']);

        $sessionType = (new SessionTypeModel())->find($data['session_type_id']);
        if ($sessionType) {
            $data['end_time'] = date('H:i:s', strtotime($data['start_time']) + $sessionType['duration_minutes'] * 60);
        }

        if (! $this->model->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(route_to('admin.slots'))->with('success', 'Slot atualizado!');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->model->find($id)) {
            return redirect()->to(route_to('admin.slots'))->with('error', 'Slot não encontrado.');
        }
        $this->model->delete($id);

        return redirect()->to(route_to('admin.slots'))->with('success', 'Slot removido.');
    }

    /**
     * Marca o slot como "reservado pelo admin" (held).
     * Suporta AJAX (retorna JSON) e requisição normal (redirect).
     */
    public function hold(int $id): \CodeIgniter\HTTP\ResponseInterface|\CodeIgniter\HTTP\RedirectResponse
    {
        $slot = $this->model->find($id);

        if (! $slot) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Slot não encontrado.'])->setStatusCode(404);
            }
            return redirect()->to(route_to('admin.slots'))->with('error', 'Slot não encontrado.');
        }

        if ($slot['status'] !== 'available') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Slot não está disponível.'])->setStatusCode(422);
            }
            return redirect()->to(route_to('admin.slots'))->with('error', 'Só é possível reservar slots disponíveis.');
        }

        $this->model->update($id, ['status' => 'held']);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'status' => 'held', 'message' => 'Slot reservado.']);
        }
        return redirect()->to(route_to('admin.slots'))->with('success', 'Slot marcado como reservado.');
    }

    /**
     * Libera um slot reservado pelo admin, tornando-o disponível novamente.
     * Suporta AJAX (retorna JSON) e requisição normal (redirect).
     */
    public function release(int $id): \CodeIgniter\HTTP\ResponseInterface|\CodeIgniter\HTTP\RedirectResponse
    {
        $slot = $this->model->find($id);

        if (! $slot) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Slot não encontrado.'])->setStatusCode(404);
            }
            return redirect()->to(route_to('admin.slots'))->with('error', 'Slot não encontrado.');
        }

        if ($slot['status'] !== 'held') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Slot não está reservado.'])->setStatusCode(422);
            }
            return redirect()->to(route_to('admin.slots'))->with('error', 'Este slot não está reservado pelo admin.');
        }

        $this->model->update($id, ['status' => 'available']);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'status' => 'available', 'message' => 'Slot liberado e disponível.']);
        }
        return redirect()->to(route_to('admin.slots'))->with('success', 'Slot liberado e disponível novamente.');
    }
}
