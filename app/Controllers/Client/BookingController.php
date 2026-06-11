<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\TimeSlotModel;
use App\Models\CustomerModel;
use App\Models\BookingModel;
use App\Models\InterestModel;

class BookingController extends BaseController
{
    public function index(): string
    {
        $slotModel = new TimeSlotModel();
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('m'));

        // Fetch ALL slots (available + booked + held) so client can see scarcity
        return view('client/booking/index', [
            'title'  => 'Agende seu Ensaio — Studio MarcoSantoFoto',
            'slots'  => $slotModel->getAllPublicByMonth($year, $month),
            'year'   => $year,
            'month'  => $month,
        ]);
    }

    public function availability(): \CodeIgniter\HTTP\ResponseInterface
    {
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('m'));
        $slots = (new TimeSlotModel())->getAvailableByMonth($year, $month);

        return $this->response->setJSON(['slots' => $slots]);
    }

    public function book(int $slotId): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $slot = (new TimeSlotModel())->getWithType($slotId);

        if ($slot === null || $slot['status'] !== 'available') {
            return redirect()->to('/')->with('error', 'Este horário não está disponível.');
        }

        return view('client/booking/form', [
            'title' => 'Confirmar Agendamento',
            'slot'  => $slot,
        ]);
    }

    public function store(int $slotId): \CodeIgniter\HTTP\RedirectResponse
    {
        $slotModel = new TimeSlotModel();
        $slot = $slotModel->getWithType($slotId);

        if ($slot === null || $slot['status'] !== 'available') {
            return redirect()->to('/')->with('error', 'Este horário não está mais disponível.');
        }

        $customerData = $this->request->getPost(['name', 'email', 'phone', 'notes']);

        // Validate
        $rules = [
            'name'  => 'required|min_length[2]',
            'email' => 'required|valid_email',
            'phone' => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Find or create customer
        $customerModel = new CustomerModel();
        $customer = $customerModel->findOrCreate([
            'name'  => $customerData['name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'],
        ]);

        // Update name/phone if customer already existed
        $customerModel->update($customer['id'], [
            'name'  => $customerData['name'],
            'phone' => $customerData['phone'],
        ]);

        // Create booking
        $bookingModel = new BookingModel();
        $bookingModel->insert([
            'time_slot_id' => $slotId,
            'customer_id'  => $customer['id'],
            'notes'        => $customerData['notes'] ?? null,
            'status'       => 'confirmed',
            'booked_at'    => date('Y-m-d H:i:s'),
        ]);

        // Mark slot as booked
        $slotModel->update($slotId, ['status' => 'booked']);

        // Send confirmation email
        $this->sendConfirmationEmail($customer, $slot);

        // Auto-login customer in session
        session()->set('customer_id', $customer['id']);

        return redirect()->to('/minha-agenda')->with('success', '✅ Agendamento confirmado! Verifique seu e-mail.');
    }

    public function myBookings(): string
    {
        $customerId   = session()->get('customer_id');
        $bookingModel = new BookingModel();
        $interestModel = new InterestModel();

        return view('client/booking/my_bookings', [
            'title'     => 'Minha Agenda',
            'bookings'  => $bookingModel->getByCustomer($customerId),
            'interests' => $interestModel->getByCustomer($customerId),
        ]);
    }

    public function cancel(int $bookingId): \CodeIgniter\HTTP\RedirectResponse
    {
        $customerId   = session()->get('customer_id');
        $bookingModel = new BookingModel();

        if (! $bookingModel->cancelBooking($bookingId, $customerId)) {
            return redirect()->back()->with('error', 'Não foi possível cancelar este agendamento.');
        }

        return redirect()->to('/minha-agenda')->with('success', 'Agendamento cancelado com sucesso.');
    }

    public function interest(int $slotId): \CodeIgniter\HTTP\RedirectResponse
    {
        $slotModel     = new TimeSlotModel();
        $slot          = $slotModel->find($slotId);

        if ($slot === null) {
            return redirect()->back()->with('error', 'Slot não encontrado.');
        }

        $post     = $this->request->getPost(['name', 'email', 'phone']);
        $rules    = ['name' => 'required', 'email' => 'required|valid_email'];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->findOrCreate([
            'name'  => $post['name'],
            'email' => $post['email'],
            'phone' => $post['phone'] ?? null,
        ]);

        $interestModel = new InterestModel();
        if (! $interestModel->hasInterest($slotId, $customer['id'])) {
            $interestModel->insert([
                'time_slot_id' => $slotId,
                'customer_id'  => $customer['id'],
            ]);
        }

        // If slot is still available but we're marking interest, update status
        if ($slot['status'] === 'available') {
            $slotModel->update($slotId, ['status' => 'interested']);
        }

        return redirect()->back()->with('success', 'Interesse registrado! Avisaremos se o horário abrir vaga.');
    }

    private function sendConfirmationEmail(array $customer, array $slot): void
    {
        try {
            $emailService = service('email');
            $emailService->setTo($customer['email']);
            $emailService->setSubject('Agendamento Confirmado — Studio MarcoSantoFoto');

            $date  = date('d/m/Y', strtotime($slot['date']));
            $start = substr($slot['start_time'], 0, 5);
            $myUrl = base_url('minha-agenda');

            $emailService->setMessage("
                <h2>Olá, {$customer['name']}! 📸</h2>
                <p>Seu ensaio fotográfico foi agendado com sucesso!</p>
                <p>
                    <strong>Sessão:</strong> {$slot['session_type_name']}<br>
                    <strong>Data:</strong> {$date}<br>
                    <strong>Horário:</strong> {$start}
                </p>
                <p><a href='{$myUrl}'>Clique aqui para ver sua agenda</a></p>
                <p>Em caso de dúvidas, entre em contato conosco.</p>
                <p>Abraços,<br>Studio MarcoSantoFoto</p>
            ");

            $emailService->send();
        } catch (\Throwable $e) {
            log_message('error', 'Email send failed: ' . $e->getMessage());
        }
    }
}
