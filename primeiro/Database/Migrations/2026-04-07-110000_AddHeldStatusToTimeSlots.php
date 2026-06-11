<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHeldStatusToTimeSlots extends Migration
{
    public function up(): void
    {
        // MySQL: alter ENUM to add 'held' status
        $this->db->query(
            "ALTER TABLE time_slots MODIFY COLUMN status ENUM('available','booked','cancelled','held') NOT NULL DEFAULT 'available'"
        );
    }

    public function down(): void
    {
        $this->db->query(
            "ALTER TABLE time_slots MODIFY COLUMN status ENUM('available','booked','cancelled') NOT NULL DEFAULT 'available'"
        );
    }
}
