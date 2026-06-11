<?php

namespace App\Controllers\Api;

use App\Models\TimeSlotModel;

class AvailabilityController extends BaseApiController
{
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('m'));

        $slots = (new TimeSlotModel())->getAvailableByMonth($year, $month);

        return $this->success($slots);
    }

    public function byDate(string $date): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->error('Formato de data inválido. Use YYYY-MM-DD.');
        }

        $slots = (new TimeSlotModel())->getAvailableByDate($date);

        return $this->success($slots);
    }
}
