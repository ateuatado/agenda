<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SessionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now  = date('Y-m-d H:i:s');
        $data = [
            [
                'name'             => 'Ensaio 1 hora',
                'duration_minutes' => 60,
                'description'      => 'Sessão fotográfica de 1 hora no studio.',
                'color'            => '#6366f1',
                'active'           => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'name'             => 'Ensaio 1h30',
                'duration_minutes' => 90,
                'description'      => 'Sessão fotográfica de 1 hora e meia no studio.',
                'color'            => '#f59e0b',
                'active'           => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'name'             => 'Ensaio 2 horas',
                'duration_minutes' => 120,
                'description'      => 'Sessão fotográfica completa de 2 horas no studio.',
                'color'            => '#10b981',
                'active'           => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'name'             => 'Ensaio Express 30min',
                'duration_minutes' => 30,
                'description'      => 'Mini session de 30 minutos.',
                'color'            => '#ec4899',
                'active'           => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        // Avoid duplicates
        $existing = $this->db->table('session_types')->countAll();
        if ($existing > 0) {
            echo "Session types already seeded, skipping." . PHP_EOL;
            return;
        }

        $this->db->table('session_types')->insertBatch($data);
        echo "Session types seeded successfully." . PHP_EOL;
    }
}
