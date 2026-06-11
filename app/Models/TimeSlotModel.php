<?php

namespace App\Models;

use CodeIgniter\Model;

class TimeSlotModel extends Model
{
    protected $table            = 'time_slots';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['session_type_id', 'date', 'start_time', 'end_time', 'status', 'notes'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'session_type_id' => 'required|integer|is_not_unique[session_types.id]',
        'date'            => 'required|valid_date[Y-m-d]',
        'start_time'      => 'required',
        'end_time'        => 'required',
        'status'          => 'permit_empty|in_list[available,booked,cancelled,held]',
    ];

    /**
     * Get available slots for a given month.
     */
    public function getAvailableByMonth(int $year, int $month): array
    {
        return $this->select('time_slots.*, session_types.name as session_type_name, session_types.duration_minutes, session_types.color')
                    ->join('session_types', 'session_types.id = time_slots.session_type_id')
                    ->where('time_slots.status', 'available')
                    ->where("YEAR(time_slots.date)", $year)
                    ->where("MONTH(time_slots.date)", $month)
                    ->where('time_slots.date >=', date('Y-m-d'))
                    ->orderBy('time_slots.date', 'ASC')
                    ->orderBy('time_slots.start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Get available slots for a specific date.
     */
    public function getAvailableByDate(string $date): array
    {
        return $this->select('time_slots.*, session_types.name as session_type_name, session_types.duration_minutes, session_types.color')
                    ->join('session_types', 'session_types.id = time_slots.session_type_id')
                    ->where('time_slots.status', 'available')
                    ->where('time_slots.date', $date)
                    ->orderBy('time_slots.start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Get ALL public-visible slots (available + booked + held) for scarcity display.
     * Excludes 'cancelled' slots. The view decides how to render each status.
     */
    public function getAllPublicByMonth(int $year, int $month): array
    {
        return $this->select('time_slots.*, session_types.name as session_type_name, session_types.duration_minutes, session_types.color')
                    ->join('session_types', 'session_types.id = time_slots.session_type_id')
                    ->whereIn('time_slots.status', ['available', 'booked', 'held'])
                    ->where("YEAR(time_slots.date)", $year)
                    ->where("MONTH(time_slots.date)", $month)
                    ->where('time_slots.date >=', date('Y-m-d'))
                    ->orderBy('time_slots.date', 'ASC')
                    ->orderBy('time_slots.start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Get slots for admin calendar view (all statuses).
     */
    public function getByMonth(int $year, int $month): array
    {
        return $this->select('time_slots.*, session_types.name as session_type_name, session_types.duration_minutes, session_types.color')
                    ->join('session_types', 'session_types.id = time_slots.session_type_id')
                    ->where("YEAR(time_slots.date)", $year)
                    ->where("MONTH(time_slots.date)", $month)
                    ->orderBy('time_slots.date', 'ASC')
                    ->orderBy('time_slots.start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Get a slot with its session type details.
     */
    public function getWithType(int $id): ?array
    {
        return $this->select('time_slots.*, session_types.name as session_type_name, session_types.duration_minutes, session_types.color, session_types.description as session_type_description')
                    ->join('session_types', 'session_types.id = time_slots.session_type_id')
                    ->where('time_slots.id', $id)
                    ->first();
    }

    /**
     * Create slots in batch for a date range.
     */
    public function createBatch(array $slots): bool
    {
        return $this->insertBatch($slots) !== false;
    }

    /**
     * Count slots by status for dashboard.
     */
    public function countByStatus(int $year, int $month): array
    {
        $result = $this->select('status, COUNT(*) as total')
                       ->where("YEAR(date)", $year)
                       ->where("MONTH(date)", $month)
                       ->groupBy('status')
                       ->findAll();

        $counts = ['available' => 0, 'booked' => 0, 'held' => 0, 'cancelled' => 0];
        foreach ($result as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }
}
