<?php

namespace App\Controllers\Admin;

use App\Models\SessionTypeModel;

class SessionTypeController extends BaseAdminController
{
    protected SessionTypeModel $model;

    public function __construct()
    {
        $this->model = new SessionTypeModel();
    }

    public function index(): string
    {
        return view('admin/session_types/index', [
            'title'        => 'Tipos de Sessão',
            'sessionTypes' => $this->model->orderBy('duration_minutes', 'ASC')->findAll(),
        ]);
    }

    public function new(): string
    {
        return view('admin/session_types/form', [
            'title'       => 'Novo Tipo de Sessão',
            'sessionType' => null,
        ]);
    }

    public function create(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->request->getPost(['name', 'duration_minutes', 'description', 'color', 'active']);
        $data['active'] = isset($data['active']) ? 1 : 0;

        if (! $this->model->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(route_to('admin.session_types'))->with('success', 'Tipo de sessão criado com sucesso!');
    }

    public function edit(int $id): string
    {
        $sessionType = $this->model->findOrFail($id);

        return view('admin/session_types/form', [
            'title'       => 'Editar Tipo de Sessão',
            'sessionType' => $sessionType,
        ]);
    }

    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->findOrFail($id);
        $data = $this->request->getPost(['name', 'duration_minutes', 'description', 'color', 'active']);
        $data['active'] = isset($data['active']) ? 1 : 0;

        if (! $this->model->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(route_to('admin.session_types'))->with('success', 'Tipo de sessão atualizado!');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->findOrFail($id);
        $this->model->delete($id);

        return redirect()->to(route_to('admin.session_types'))->with('success', 'Tipo de sessão removido.');
    }
}
