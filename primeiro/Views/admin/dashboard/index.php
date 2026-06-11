<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<!-- Stats row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="stat-value"><?= $slotCounts['available'] ?></div>
                <div class="stat-label">Slots disponíveis</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-journal-check"></i></div>
            <div>
                <div class="stat-value"><?= $slotCounts['booked'] ?></div>
                <div class="stat-label">Slots agendados</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="stat-value"><?= $upcomingBookings ?></div>
                <div class="stat-label">Próximos ensaios</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-value"><?= $totalCustomers ?></div>
                <div class="stat-label">Clientes cadastrados</div>
            </div>
        </div>
    </div>
</div>

<!-- Recent bookings -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-journal-check me-2 text-primary"></i>Próximos Agendamentos</span>
        <a href="<?= route_to('admin.bookings') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Tipo de Sessão</th>
                    <th>Cliente</th>
                    <th>E-mail</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentBookings)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum agendamento encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach (array_slice($recentBookings, 0, 10) as $booking): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($booking['date'])) ?></td>
                            <td><?= substr($booking['start_time'], 0, 5) ?></td>
                            <td><?= esc($booking['session_type_name']) ?></td>
                            <td><?= esc($booking['customer_name']) ?></td>
                            <td><small class="text-muted"><?= esc($booking['customer_email']) ?></small></td>
                            <td>
                                <span class="badge <?= 'badge-' . $booking['status'] ?> rounded-pill px-2 py-1">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
