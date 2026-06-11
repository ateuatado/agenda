<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['name', 'email', 'phone', 'token', 'token_expires_at'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[150]',
        'email' => 'required|valid_email|max_length[150]',
        'phone' => 'permit_empty|min_length[8]|max_length[20]',
    ];

    protected $validationMessages = [
        'name'  => ['required' => 'O nome é obrigatório.'],
        'email' => ['required' => 'O e-mail é obrigatório.', 'valid_email' => 'Informe um e-mail válido.'],
    ];

    /**
     * Find customer by email.
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Find customer by magic link token.
     */
    public function findByToken(string $token): ?array
    {
        return $this->where('token', $token)
                    ->where('token_expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Generate and save a new magic link token for a customer.
     */
    public function generateToken(int $customerId): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update($customerId, [
            'token'            => $token,
            'token_expires_at' => $expires,
        ]);

        return $token;
    }

    /**
     * Invalidate the token after use.
     */
    public function invalidateToken(int $customerId): void
    {
        $this->update($customerId, [
            'token'            => null,
            'token_expires_at' => null,
        ]);
    }

    /**
     * Find or create a customer by email.
     */
    public function findOrCreate(array $data): array
    {
        $customer = $this->findByEmail($data['email']);

        if ($customer === null) {
            $this->insert($data);
            $customer = $this->find($this->getInsertID());
        }

        return $customer;
    }
}
