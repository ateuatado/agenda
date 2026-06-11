<?= $this->extend('client/layouts/main') ?>

<?= $this->section('hero') ?>
<div class="client-hero">
    <div>
        <h1>Agende seu Ensaio</h1>
        <p class="subtitle">Escolha o melhor horário para o seu ensaio fotográfico.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $prev = $month === 1 ? ['year' => $year - 1, 'month' => 12] : ['year' => $year, 'month' => $month - 1];
    $next = $month === 12 ? ['year' => $year + 1, 'month' => 1] : ['year' => $year, 'month' => $month + 1];
    $monthNames = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $dayNames   = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];

    function formatDatePtBR(string $date, array $dayNames, array $monthNames): string {
        $ts = strtotime($date);
        $dow = (int) date('w', $ts);
        $d   = (int) date('j', $ts);
        $m   = (int) date('n', $ts);
        return $dayNames[$dow] . ', ' . $d . ' de ' . $monthNames[$m - 1];
    }
?>

<div class="month-nav">
    <a href="?year=<?= $prev['year'] ?>&month=<?= $prev['month'] ?>" class="nav-arrow">
        <i class="bi bi-chevron-left"></i>
    </a>
    <span class="month-label"><?= $monthNames[$month - 1] ?> <?= $year ?></span>
    <a href="?year=<?= $next['year'] ?>&month=<?= $next['month'] ?>" class="nav-arrow">
        <i class="bi bi-chevron-right"></i>
    </a>
</div>

<?php if (empty($slots)): ?>
    <div class="empty-state">
        <div class="empty-icon">◻</div>
        <h3>Nenhum horário disponível para este mês.</h3>
        <p>Tente outro mês ou entre em contato conosco.</p>
    </div>
<?php else: ?>
    <?php
        $byDate = [];
        foreach ($slots as $slot) {
            $byDate[$slot['date']][] = $slot;
        }
    ?>
    <?php foreach ($byDate as $date => $daySlots): ?>
        <div class="day-group">
            <div class="day-label">
                <?= formatDatePtBR($date, $dayNames, $monthNames) ?>
            </div>
            <div class="slot-grid">
                <?php foreach ($daySlots as $slot):
                    $isAvailable = $slot['status'] === 'available';
                ?>
                    <?php if ($isAvailable): ?>
                        <a href="<?= route_to('client.book', $slot['id']) ?>" class="slot-card available">
                            <div class="slot-time"><?= substr($slot['start_time'], 0, 5) ?></div>
                            <div class="slot-type-name"><?= esc($slot['session_type_name']) ?></div>
                            <div class="slot-duration">
                                <i class="bi bi-clock" style="font-size:.6rem;margin-right:.3rem"></i><?= \App\Models\SessionTypeModel::formatDuration($slot['duration_minutes']) ?>
                            </div>
                            <span class="slot-status-tag available">Disponível</span>
                        </a>
                    <?php else: ?>
                        <div class="slot-card occupied">
                            <div class="slot-time"><?= substr($slot['start_time'], 0, 5) ?></div>
                            <div class="slot-type-name"><?= esc($slot['session_type_name']) ?></div>
                            <div class="slot-duration">
                                <i class="bi bi-clock" style="font-size:.6rem;margin-right:.3rem"></i><?= \App\Models\SessionTypeModel::formatDuration($slot['duration_minutes']) ?>
                            </div>
                            <span class="slot-status-tag occupied">Agendado</span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
