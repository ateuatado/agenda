<?php

namespace App\Filters;

use App\Models\CustomerModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Customer Session Auth Filter
 * Validates customer session for web portal protected pages.
 */
class CustomerAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->has('customer_id')) {
            return redirect()->to('/acesso')->with('warning', 'Faça login para acessar sua agenda.');
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->find($session->get('customer_id'));

        if ($customer === null) {
            $session->remove('customer_id');
            return redirect()->to('/acesso')->with('warning', 'Sessão expirada. Faça login novamente.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
