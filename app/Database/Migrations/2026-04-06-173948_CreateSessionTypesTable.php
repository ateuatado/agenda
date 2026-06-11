<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSessionTypesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'duration_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'comment'    => 'Duration in minutes (e.g. 60, 90, 120)',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#6366f1',
                'comment'    => 'Hex color for calendar display',
            ],
            'active' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('session_types');
    }

    public function down(): void
    {
        $this->forge->dropTable('session_types');
    }
}
