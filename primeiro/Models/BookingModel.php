<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['time_slot_id', 'customer_id', 'notes', 'status', 'booked_at', 'cancelled_at'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'time_slot_id' => 'required|integer',
        'customer_id'  => 'required|integer',
        'status'       => 'permit_empty|in_list[pending,confirmed,cancelled]',
    ];

    /**
     * Get all bookings with slot and customer info.
     */
    public function getWithDetails(?string $status = null): array
    {
        $builder = $this->select('bookings.*, time_slots.date, time_slots.start_time, time_slots.end_time, session_types.name as session_type_name, customers.name as customer_name, customers.email as customer_email, customers.phone as customer_phone')
                        ->join('time_slots', 'time_slots.id = bookings.time_slot_id')
                        ->join('session_types', 'session_types.id = time_slots.session_type_id')
                        ->join('customers', 'customers.id = bookings.customer_id');

        if ($status !== null) {
            $builder->where('bookings.status', $status);
        }

        return $builder->orderBy('time_slots.date', 'ASC')
                       ->orderBy('time_slots.start_time', 'ASC')
                       ->findAll();
    }

    /**
     * Get bookings for a specific customer.
     */
    public function getByCustomer(int $customerId): array
    {
        return $this->select('bookings.*, time_slots.date, time_slots.start_time, time_slots.end_time, session_types.name as session_type_name, session_types.color, session_types.duration_minutes')
                    ->join('time_slots', 'time_slots.id = bookings.time_slot_id')
                    ->join('session_types', 'session_types.id = time_slots.session_type_id')
                    ->where('bookings.customer_id', $customerId)
                    ->where('bookings.status !=', 'cancelled')
                    ->orderBy('time_slots.date', 'ASC')
                    ->findAll();
    }

    /**
     * Cancel a booking and free the time slot.
     */
    public function cancelBooking(int $bookingId, int $customerId): bool
    {
        $booking = $this->where('id', $bookingId)
                        ->where('customer_id', $customerId)
                        ->where('status !=', 'cancelled')
                        ->first();

        if ($booking === null) {
            return false;
        }

        // Update booking status
        $this->update($bookingId, [
            'status'       => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);

        // Free the time slot
        $slotModel = new TimeSlotModel();
        $slotModel->update($booking['time_slot_id'], ['status' => 'available']);

        // Notify next interested customer
        $this->notifyNextInterested($booking['time_slot_id']);

        return true;
    }

    /**
     * Notify the next interested customer when a slot becomes available.
     */
    private function notifyNextInterested(int $slotId): void
    {
        $interestModel = new InterestModel();
        $next = $interestModel->getNextNotified($slotId);

        if ($next === null) {
            return;
        }

        $slotModel    = new TimeSlotModel();
        $slot         = $slotModel->getWithType($slotId);
        $customerModel = new CustomerModel();
        $customer     = $customerModel->find($next['customer_id']);

        if ($slot === null || $customer === null) {
            return;
        }

        // Send email notification
        $emailService = service('email');
        $emailService->setTo($customer['email']);
        $emailService->setSubject('Um horário ficou disponível! — Studio MarcoSantoFoto');

        $date     = date('d/m/Y', strtotime($slot['date']));
        $start    = substr($slot['start_time'], 0, 5);
        $baseUrl  = rtrim(base_url(), '/');
        $bookUrl  = "{$baseUrl}/agendar/{$slot['id']}";

        $emailService->setMessage("
            <h2>Boa notícia, {$customer['name']}!</h2>
            <p>Um horário que você marcou interesse ficou disponível:</p>
            <p><strong>{$slot['session_type_name']}</strong><br>
            📅 {$date} às {$start}</p>
            <p><a href='{$bookUrl}'>Clique aqui para agendar agora</a></p>
            <p>Este link pode expirar se outra pessoa agendar antes.</p>
            <p>Studio MarcoSantoFoto</p>
        ");

        $emailService->send();

        // Mark as notified
        $interestModel->markNotified($next['id']);
    }

    /**
     * Count upcoming bookings (for dashboard).
     */
    public function countUpcoming(): int
    {
        return $this->join('time_slots', 'time_slots.id = bookings.time_slot_id')
                    ->where('bookings.status', 'confirmed')
                    ->where('time_slots.date >=', date('Y-m-d'))
                    ->countAllResults();
    }
}
