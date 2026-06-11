<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingTokenModel extends Model
{
    protected $table         = 'booking_tokens';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'token', 'order_id', 'customer_email', 'customer_name',
        'customer_phone', 'session_type_id', 'used_count', 'expires_at', 'created_at',
    ];

    /**
     * Gera e persiste um novo token de acesso.
     * Retorna o registro completo (incluindo o token gerado).
     */
    public function generate(array $data, int $expireDays = 60): array
    {
        $token = bin2hex(random_bytes(32)); // 64 chars, criptograficamente seguro

        $row = [
            'token'           => $token,
            'order_id'        => $data['order_id']        ?? null,
            'customer_email'  => $data['customer_email'],
            'customer_name'   => $data['customer_name'],
            'customer_phone'  => $data['customer_phone']  ?? null,
            'session_type_id' => $data['session_type_id'] ?? null,
            'used_count'      => 0,
            'expires_at'      => date('Y-m-d H:i:s', strtotime("+{$expireDays} days")),
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $this->insert($row);
        return $row;
    }

    /**
     * Busca um token válido (não expirado).
     * Também incrementa used_count para tracking.
     */
    public function findValid(string $token): ?array
    {
        $record = $this
            ->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        if ($record) {
            $this->update($record['id'], ['used_count' => $record['used_count'] + 1]);
        }

        return $record;
    }

    /**
     * Busca todos os tokens de um pedido (para listar no painel hero).
     */
    public function findByOrder(string $orderId): array
    {
        return $this->where('order_id', $orderId)->findAll();
    }
}
