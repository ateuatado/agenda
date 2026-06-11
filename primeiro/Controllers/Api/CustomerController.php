<?php

namespace App\Controllers\Api;

use App\Models\CustomerModel;

class CustomerController extends BaseApiController
{
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $customers = (new CustomerModel())->orderBy('created_at', 'DESC')->findAll();
        return $this->success($customers);
    }
}
