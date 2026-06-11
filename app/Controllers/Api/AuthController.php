<?php

namespace App\Controllers\Api;

use App\Models\CustomerModel;

class AuthController extends BaseApiController
{
    public function requestAccess(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data  = $this->request->getJSON(true);
        $email = $data['email'] ?? '';

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('E-mail inválido.', 422);
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->findByEmail($email);

        if ($customer === null) {
            // Don't reveal that the email doesn't exist
            return $this->success(null, 'Se este e-mail estiver cadastrado, você receberá o link em instantes.');
        }

        $token = $customerModel->generateToken($customer['id']);
        $link  = base_url("confirmar/{$token}");

        // Send email
        try {
            $emailService = service('email');
            $emailService->setTo($customer['email']);
            $emailService->setSubject('Seu link de acesso — Studio MarcoSantoFoto');
            $emailService->setMessage("<p>Clique para acessar: <a href='{$link}'>{$link}</a></p><p>Expira em 1 hora.</p>");
            $emailService->send();
        } catch (\Throwable $e) {
            log_message('error', 'Magic link API email: ' . $e->getMessage());
        }

        return $this->success(null, 'Link de acesso enviado.');
    }

    public function verify(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data  = $this->request->getJSON(true);
        $token = $data['token'] ?? '';

        $customerModel = new CustomerModel();
        $customer = $customerModel->findByToken($token);

        if ($customer === null) {
            return $this->error('Token inválido ou expirado.', 401);
        }

        // Generate a new long-lived access token for API use
        $accessToken = $customerModel->generateToken($customer['id']);

        return $this->success([
            'access_token' => $accessToken,
            'customer'     => [
                'id'    => $customer['id'],
                'name'  => $customer['name'],
                'email' => $customer['email'],
            ],
        ], 'Autenticado com sucesso.');
    }
}
