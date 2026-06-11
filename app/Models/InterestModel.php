<?php

namespace App\Models;

use CodeIgniter\Model;

class InterestModel extends Model
{
    protected $table            = 'interests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['time_slot_id', 'customer_id', 'notified_at'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = false;

    protected $validationRules = [
        'time_slot_id' => 'required|integer',
        'customer_id'  => 'required|integer',
    ];

    /**
     * Check if a customer already expressed interest in a slot.
     */
    public function hasInterest(int $slotId, int $customerId): bool
    {
        return $this->where('time_slot_id', $slotId)
                    ->where('customer_id', $customerId)
                    ->countAllResults() > 0;
    }

    /**
     * Get the next un-notified interested customer for a slot (FIFO).
     */
    public function getNextNotified(int $slotId): ?array
    {
        return $this->where('time_slot_id', $slotId)
                    ->where('notified_at', null)
                    ->orderBy('created_at', 'ASC')
                    ->first();
    }

    /**
     * Mark an interest record as notified.
     */
    public function markNotified(int $interestId): void
    {
        $this->update($interestId, ['notified_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get all interests for a customer with slot details.
     */
    public function getByCustomer(int $customerId): array
    {
        return $this->select('interests.*, time_slots.date, time_slots.start_time, time_slots.end_time, time_slots.status, session_types.name as session_type_name, session_types.color')
                    ->join('time_slots', 'time_slots.id = interests.time_slot_id')
                    ->join('session_types', 'session_types.id = time_slots.session_type_id')
                    ->where('interests.customer_id', $customerId)
                    ->orderBy('time_slots.date', 'ASC')
                    ->findAll();
    }

    /**
     * Count interests per slot (for admin view).
     */
    public function getCounts(): array
    {
        $result = $this->select('time_slot_id, COUNT(*) as total')
                       ->groupBy('time_slot_id')
                       ->findAll();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['time_slot_id']] = (int) $row['total'];
        }

        return $counts;
    }
}
