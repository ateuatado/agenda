<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInterestsTable extends Migration
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
            'time_slot_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'customer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'notified_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When the customer was notified about slot availability',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['time_slot_id', 'customer_id']);
        $this->forge->addForeignKey('time_slot_id', 'time_slots', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('interests');
    }

    public function down(): void
    {
        $this->forge->dropTable('interests');
    }
}
