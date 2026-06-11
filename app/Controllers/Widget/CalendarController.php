<?php

namespace App\Controllers\Widget;

use App\Controllers\BaseController;
use App\Models\TimeSlotModel;

/**
 * CalendarController — Retorna HTML puro (sem layout) para embed em qualquer aplicação.
 *
 * Uso:
 *   fetch('https://agenda.marcosantofoto.com.br/widget/calendar?year=2026&month=6')
 *     .then(r => r.text())
 *     .then(html => document.getElementById('meu-container').innerHTML = html);
 *
 * Query params:
 *   year    int   Ano (default: atual)
 *   month   int   Mês 1-12 (default: atual)
 *   base    str   URL base para links de agendamento (default: URL da agenda)
 *   theme   str   'dark' | 'light' (default: 'light') — aplica data-theme no container
 */
class CalendarController extends BaseController
{
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $slotModel = new TimeSlotModel();
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('m'));
        $base  = rtrim($this->request->getGet('base') ?? base_url('agendar'), '/');
        $theme = in_array($this->request->getGet('theme'), ['dark', 'light'], true)
                    ? $this->request->getGet('theme')
                    : 'light';

        // Clamp to not show past months
        $minY = (int) date('Y');
        $minM = (int) date('m');
        if ($year < $minY || ($year === $minY && $month < $minM)) {
            $year  = $minY;
            $month = $minM;
        }

        $slots = $slotModel->getAllPublicByMonth($year, $month);

        // Group by date
        $slotsByDate = [];
        foreach ($slots as $slot) {
            $slotsByDate[$slot['date']][] = $slot;
        }

        // Build calendar matrix
        $firstDayTs  = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = (int) date('t', $firstDayTs);
        $startDow    = (int) date('w', $firstDayTs);
        $today       = date('Y-m-d');

        $calendar = [];
        $day      = 1;
        for ($week = 0; $week < 6; $week++) {
            $weekData = [];
            for ($dow = 0; $dow < 7; $dow++) {
                $cellIndex = $week * 7 + $dow;
                if ($cellIndex < $startDow || $day > $daysInMonth) {
                    $weekData[] = null;
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
            if ($day > $daysInMonth) break;
        }

        $prev = $month === 1 ? ['year' => $year - 1, 'month' => 12] : ['year' => $year, 'month' => $month - 1];
        $next = $month === 12 ? ['year' => $year + 1, 'month' => 1] : ['year' => $year, 'month' => $month + 1];

        $nowYM  = $minY * 12 + $minM;
        $prevYM = $prev['year'] * 12 + $prev['month'];
        if ($prevYM < $nowYM) $prev = null;

        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',    4 => 'Abril',
            5 => 'Maio',    6 => 'Junho',      7 => 'Julho',    8 => 'Agosto',
            9 => 'Setembro',10 => 'Outubro',  11 => 'Novembro',12 => 'Dezembro',
        ];

        $html = view('widget/calendar', [
            'calendar'    => $calendar,
            'slots'       => $slots,
            'year'        => $year,
            'month'       => $month,
            'monthName'   => $monthNames[$month],
            'prev'        => $prev,
            'next'        => $next,
            'base'        => $base,
            'theme'       => $theme,
            'widgetCssUrl'=> base_url('assets/css/widget.css'),
        ]);

        // Return HTML fragment with CORS headers for cross-origin embedding
        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET')
            ->setHeader('Cache-Control', 'public, max-age=60')
            ->setBody($html);
    }
}
