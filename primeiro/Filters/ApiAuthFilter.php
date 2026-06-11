<?php

namespace App\Filters;

use App\Models\CustomerModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Customer API Auth Filter
 * Validates Bearer token for customer API requests.
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = $this->extractToken($request);

        if ($token === null) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Token de autenticação necessário.']);
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->findByToken($token);

        if ($customer === null) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Token inválido ou expirado.']);
        }

        // Store customer in request for use in controllers
        $request->customer = $customer;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function extractToken(RequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // Also check session for web portal
        $session = session();
        if ($session->has('customer_token')) {
            return $session->get('customer_token');
        }

        return null;
    }
}
