<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\CustomerModel;

class AuthController extends BaseController
{
    public function requestAccess(): string
    {
        return view('client/auth/request_access', [
            'title' => 'Acessar Minha Agenda',
        ]);
    }

    public function sendMagicLink(): \CodeIgniter\HTTP\RedirectResponse
    {
        $email = $this->request->getPost('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Informe um e-mail válido.');
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->findByEmail($email);

        if ($customer === null) {
            // Don't reveal that the email doesn't exist (security)
            return redirect()->back()->with('success', 'Se este e-mail estiver cadastrado, você receberá o link em instantes.');
        }

        $token = $customerModel->generateToken($customer['id']);
        $this->sendMagicLinkEmail($customer, $token);

        return redirect()->back()->with('success', 'Link de acesso enviado! Verifique seu e-mail.');
    }

    public function confirm(string $token): \CodeIgniter\HTTP\RedirectResponse
    {
        $customerModel = new CustomerModel();
        $customer = $customerModel->findByToken($token);

        if ($customer === null) {
            return redirect()->to('/acesso')->with('error', 'Link inválido ou expirado. Solicite um novo link.');
        }

        // Log customer in via session
        session()->set('customer_id', $customer['id']);

        // Invalidate token after use
        $customerModel->invalidateToken($customer['id']);

        return redirect()->to('/minha-agenda')->with('success', 'Bem-vindo(a), ' . $customer['name'] . '!');
    }

    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->remove('customer_id');
        return redirect()->to('/')->with('success', 'Você saiu da sua conta.');
    }

    private function sendMagicLinkEmail(array $customer, string $token): void
    {
        try {
            $emailService = service('email');
            $emailService->setTo($customer['email']);
            $emailService->setSubject('Seu link de acesso — Studio MarcoSantoFoto');

            $link = base_url("confirmar/{$token}");

            $emailService->setMessage("
                <h2>Olá, {$customer['name']}!</h2>
                <p>Clique no link abaixo para acessar sua agenda:</p>
                <p><a href='{$link}' style='background:#6366f1;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;display:inline-block;'>Acessar Minha Agenda</a></p>
                <p><small>Este link expira em 1 hora.</small></p>
                <p>Se não solicitou este link, ignore este e-mail.</p>
                <p>Studio MarcoSantoFoto</p>
            ");

            $emailService->send();
        } catch (\Throwable $e) {
            log_message('error', 'Magic link email failed: ' . $e->getMessage());
        }
    }
}
