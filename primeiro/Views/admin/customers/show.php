<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center p-4">
                <div class="mb-3" style="font-size:3rem">👤</div>
                <h5 class="fw-700 mb-1"><?= esc($customer['name']) ?></h5>
                <p class="text-muted small mb-2"><?= esc($customer['email']) ?></p>
                <p class="text-muted small"><?= esc($customer['phone'] ?? '—') ?></p>
                <small class="text-muted">Desde <?= date('d/m/Y', strtotime($customer['created_at'])) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Agendamentos do cliente</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Data</th><th>Horário</th><th>Sessão</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Nenhum agendamento.</td></tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($b['date'])) ?></td>
                                    <td><?= substr($b['start_time'], 0, 5) ?></td>
                                    <td><?= esc($b['session_type_name']) ?></td>
                                    <td><span class="badge badge-<?= $b['status'] ?> rounded-pill"><?= ucfirst($b['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
