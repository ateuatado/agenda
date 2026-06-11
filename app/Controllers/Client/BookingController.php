<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\TimeSlotModel;
use App\Models\CustomerModel;
use App\Models\BookingModel;
use App\Models\InterestModel;

class BookingController extends BaseController
{
    /**
     * Valida chave de acesso (?via=CHAVE) e armazena permissão na sessão por 2h.
     * Retorna true se o visitante tem permissão para agendar.
     */
    private function checkBookingAccess(): bool
    {
        $configKey = env('booking.accessKey', '');

        // Se não há chave configurada, acesso livre
        if (empty($configKey)) {
            return true;
        }

        $sess = session();

        // Valida chave na URL e armazena na sessão por 2h
        $viaKey = $this->request->getGet('via');
        if ($viaKey && hash_equals($configKey, $viaKey)) {
            $sess->set('booking_access', true);
            $sess->set('booking_access_until', time() + 7200); // 2 horas
        }

        // Verifica sessão válida
        if ($sess->get('booking_access') && $sess->get('booking_access_until') > time()) {
            return true;
        }

        // Admin Shield também pode agendar
        if (auth()->loggedIn()) {
            return true;
        }

        return false;
    }

    public function index(): string
    {
        $slotModel = new TimeSlotModel();
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('m'));

        // Clamp: don't show past months
        $now = date('Y-m');
        if (sprintf('%04d-%02d', $year, $month) < $now) {
            $year  = (int) date('Y');
            $month = (int) date('m');
        }

        $canBook = $this->checkBookingAccess();

        // Fetch ALL public slots (available + booked + held) for scarcity display
        $slots = $slotModel->getAllPublicByMonth($year, $month);

        // Group slots by date
        $slotsByDate = [];
        foreach ($slots as $slot) {
            $slotsByDate[$slot['date']][] = $slot;
        }

        // Build calendar matrix: array of weeks → 7 cells each
        $firstDayTs  = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = (int) date('t', $firstDayTs);
        $startDow    = (int) date('w', $firstDayTs); // 0=Sun, 6=Sat
        $today       = date('Y-m-d');

        $calendar = [];
        $day      = 1;

        for ($week = 0; $week < 6; $week++) {
            $weekData = [];
            for ($dow = 0; $dow < 7; $dow++) {
                $cellIndex = $week * 7 + $dow;
                if ($cellIndex < $startDow || $day > $daysInMonth) {
                    $weekData[] = null; // empty padding cell
                } else {
                    $dateStr  = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $daySlots = $slotsByDate[$dateStr] ?? [];
                    $available = count(array_filter($daySlots, fn($s) => $s['status'] === 'available'));

                    $weekData[] = [
                        'day'       => $day,
                        'date'      => $dateStr,
                        'slots'     => $daySlots,
                        'available' => $available,
                        'total'     => count($daySlots),
                        'isToday'   => $dateStr === $today,
                        'isPast'    => $dateStr < $today,
                    ];
                    $day++;
                }
            }
            $calendar[] = $weekData;
            if ($day > $daysInMonth) {
                break;
            }
        }

        // Previous / next month navigation
        $prev = $month === 1
            ? ['year' => $year - 1, 'month' => 12]
            : ['year' => $year,     'month' => $month - 1];
        $next = $month === 12
            ? ['year' => $year + 1, 'month' => 1]
            : ['year' => $year,     'month' => $month + 1];

        // Don't allow navigating to past months
        $nowYM = date('Y') * 12 + date('m');
        $prevYM = $prev['year'] * 12 + $prev['month'];
        if ($prevYM < $nowYM) {
            $prev = null;
        }

        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',    4 => 'Abril',
            5 => 'Maio',    6 => 'Junho',      7 => 'Julho',    8 => 'Agosto',
            9 => 'Setembro',10 => 'Outubro',  11 => 'Novembro',12 => 'Dezembro',
        ];

        return view('client/booking/index', [
            'title'      => 'Agende seu Ensaio — Studio MarcoSantoFoto',
            'calendar'   => $calendar,
            'slots'      => $slots,
            'year'       => $year,
            'month'      => $month,
            'monthName'  => $monthNames[$month],
            'prev'       => $prev,
            'next'       => $next,
            'canBook'    => $canBook,
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
        if (! $this->checkBookingAccess()) {
            return redirect()->to('/')
                ->with('warning', 'Para agendar, acesse a agenda pelo nosso site.');
        }

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

        $rules = [
            'name'  => 'required|min_length[2]',
            'email' => 'required|valid_email',
            'phone' => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->findOrCreate([
            'name'  => $customerData['name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'],
        ]);

        // Update name/phone in case customer already existed with different data
        $customerModel->update($customer['id'], [
            'name'  => $customerData['name'],
            'phone' => $customerData['phone'],
        ]);

        $bookingModel = new BookingModel();
        $bookingModel->insert([
            'time_slot_id' => $slotId,
            'customer_id'  => $customer['id'],
            'notes'        => $customerData['notes'] ?? null,
            'status'       => 'confirmed',
            'booked_at'    => date('Y-m-d H:i:s'),
        ]);

        $slotModel->update($slotId, ['status' => 'booked']);
        $this->sendConfirmationEmail($customer, $slot);
        session()->set('customer_id', $customer['id']);

        return redirect()->to('/minha-agenda')->with('success', '✅ Agendamento confirmado! Verifique seu e-mail.');
    }

    public function myBookings(): string
    {
        $customerId    = session()->get('customer_id');
        $bookingModel  = new BookingModel();
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
        $slotModel = new TimeSlotModel();
        $slot      = $slotModel->find($slotId);

        if ($slot === null) {
            return redirect()->back()->with('error', 'Slot não encontrado.');
        }

        $post  = $this->request->getPost(['name', 'email', 'phone']);
        $rules = ['name' => 'required', 'email' => 'required|valid_email'];

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

        // Bug fix: DO NOT change slot status to 'interested' — that value is not in the enum.
        // The slot remains 'available' (or 'booked') and the interest is tracked in the interests table.

        return redirect()->back()->with('success', 'Interesse registrado! Avisaremos quando este horário abrir vaga.');
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
