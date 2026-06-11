<?php

namespace App\Controllers\Admin;

class SettingsController extends BaseAdminController
{
    public function index(): string
    {
        return view('admin/settings/index', [
            'title' => 'Configurações',
        ]);
    }

    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        // Future: save settings via CodeIgniter Settings library
        return redirect()->to(route_to('admin.settings'))->with('success', 'Configurações salvas.');
    }
}
