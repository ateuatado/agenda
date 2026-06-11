<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTimeSlotsTable extends Migration
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
            'session_type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'start_time' => [
                'type' => 'TIME',
            ],
            'end_time' => [
                'type' => 'TIME',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'booked', 'blocked', 'interested'],
                'default'    => 'available',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Internal admin notes',
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
        $this->forge->addKey(['date', 'status']);
        $this->forge->addForeignKey('session_type_id', 'session_types', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('time_slots');
    }

    public function down(): void
    {
        $this->forge->dropTable('time_slots');
    }
}
