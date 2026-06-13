<?php

namespace App\Controllers\Api;

use App\Models\BookingTokenModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AccessTokenController
 *
 * Endpoint chamado pelo Hero após pagamento confirmado.
 * Gera um token único que dá ao cliente o direito de agendar.
 *
 * POST /api/v1/access-tokens
 * Authorization: Bearer {booking.apiKey}
 */
class AccessTokenController extends BaseApiController
{
    /**
     * Cria um token de acesso vinculado a uma compra no Hero.
     *
     * Body JSON esperado:
     * {
     *   "order_id":       "HERO_ORDER_123",   // ID do pedido no hero (obrigatório)
     *   "customer_email": "cliente@email.com", // (obrigatório)
     *   "customer_name":  "Maria Silva",       // (obrigatório)
     *   "customer_phone": "11999999999",       // (opcional)
     *   "session_type_id": 1,                  // (opcional) tipo de sessão comprado
     *   "expires_days":   60                   // (opcional) padrão: 60 dias
     * }
     */
    public function create(): ResponseInterface
    {
        // ── Autenticação por API Key ───────────────────────────────────────
        $configKey = env('booking.apiKey', '');
        if (empty($configKey)) {
            return $this->error('API key não configurada no servidor.', 500);
        }

        $authHeader = $this->request->getHeaderLine('Authorization');
        $provided   = str_starts_with($authHeader, 'Bearer ') ? substr($authHeader, 7) : '';

        if (! hash_equals($configKey, $provided)) {
            return $this->error('API key inválida.', 401);
        }

        // ── Validação do body ─────────────────────────────────────────────
        $data = $this->request->getJSON(true) ?? [];

        $rules = [
            'order_id'       => 'required|max_length[100]',
            'customer_email' => 'required|valid_email|max_length[191]',
            'customer_name'  => 'required|min_length[2]|max_length[100]',
            'customer_phone' => 'permit_empty|max_length[30]',
            'expires_days'   => 'permit_empty|integer|greater_than[0]|less_than[366]',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->error('Dados inválidos.', 422, $this->validator->getErrors());
        }

        // ── Geração do token ──────────────────────────────────────────────
        $expireDays = (int) ($data['expires_days'] ?? 60);
        $model      = new BookingTokenModel();
        $record     = $model->generate($data, $expireDays);

        // ── Resposta ──────────────────────────────────────────────────────
        return $this->json([
            'success'    => true,
            'token'      => $record['token'],
            'link'       => base_url('/?token=' . $record['token']),
            'expires_at' => $record['expires_at'],
            'order_id'   => $record['order_id'],
        ], 201);
    }

    /**
     * Verifica se um token ainda é válido (útil para o hero checar status).
     *
     * GET /api/v1/access-tokens/{token}
     */
    public function show(string $token): ResponseInterface
    {
        // Mesma autenticação
        $configKey  = env('booking.apiKey', '');
        $authHeader = $this->request->getHeaderLine('Authorization');
        $provided   = str_starts_with($authHeader, 'Bearer ') ? substr($authHeader, 7) : '';

        if (empty($configKey) || ! hash_equals($configKey, $provided)) {
            return $this->error('API key inválida.', 401);
        }

        $model  = new BookingTokenModel();
        $record = $model->where('token', $token)->first();

        if (! $record) {
            return $this->error('Token não encontrado.', 404);
        }

        $isValid = strtotime($record['expires_at']) > time();

        return $this->json([
            'token'      => $record['token'],
            'order_id'   => $record['order_id'],
            'valid'      => $isValid,
            'used_count' => (int) $record['used_count'],
            'expires_at' => $record['expires_at'],
        ]);
    }
}
