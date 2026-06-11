<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionTypeModel extends Model
{
    protected $table            = 'session_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['name', 'duration_minutes', 'description', 'color', 'active'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'name'             => 'required|min_length[3]|max_length[100]',
        'duration_minutes' => 'required|integer|greater_than[0]',
        'color'            => 'permit_empty|exact_length[7]|regex_match[/^#[0-9A-Fa-f]{6}$/]',
    ];

    protected $validationMessages = [
        'name'             => ['required' => 'O nome do tipo de sessão é obrigatório.'],
        'duration_minutes' => ['required' => 'A duração é obrigatória.', 'integer' => 'A duração deve ser em minutos.'],
    ];

    /**
     * Get all active session types.
     */
    public function getActive(): array
    {
        return $this->where('active', 1)->orderBy('duration_minutes', 'ASC')->findAll();
    }

    /**
     * Format duration in a human-readable string.
     */
    public static function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} minutos";
        }

        $hours   = intdiv($minutes, 60);
        $mins    = $minutes % 60;
        $label   = $hours === 1 ? '1 hora' : "{$hours} horas";

        return $mins > 0 ? "{$label} e {$mins} min" : $label;
    }
}
