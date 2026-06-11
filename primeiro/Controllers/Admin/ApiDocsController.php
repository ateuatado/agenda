<?php

namespace App\Controllers\Admin;

class ApiDocsController extends BaseAdminController
{
    public function index(): string
    {
        return view('admin/api_docs/index', [
            'title' => 'Documentação da API',
            'baseUrl' => rtrim(base_url(), '/'),
        ]);
    }
}
