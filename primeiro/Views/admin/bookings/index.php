<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<!-- Filters -->
<div class="d-flex gap-2 mb-3 flex-wrap">
    <?php foreach (['' => 'Todos', 'confirmed' => 'Confirmados', 'cancelled' => 'Cancelados', 'pending' => 'Pendentes'] as $val => $label): ?>
        <a href="?<?= $val ? 'status='.$val : '' ?>"
           class="btn btn-sm <?= ($status ?? '') === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-journal-check me-2 text-primary"></i>Agendamentos</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Data</th><th>Horário</th><th>Sessão</th><th>Cliente</th><th>Telefone</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhum agendamento encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td class="text-muted small">#<?= $b['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($b['date'])) ?></td>
                            <td><?= substr($b['start_time'], 0, 5) ?></td>
                            <td><?= esc($b['session_type_name']) ?></td>
                            <td>
                                <a href="<?= route_to('admin.customers.show', $b['customer_id']) ?>" class="text-decoration-none">
                                    <?= esc($b['customer_name']) ?>
                                </a>
                                <div class="text-muted small"><?= esc($b['customer_email']) ?></div>
                            </td>
                            <td class="text-muted small"><?= esc($b['customer_phone']) ?></td>
                            <td>
                                <span class="badge badge-<?= $b['status'] ?> rounded-pill px-2 py-1">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form action="<?= route_to('admin.bookings.status', $b['id']) ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto"
                                        onchange="this.form.submit()">
                                        <?php foreach (['confirmed','pending','cancelled'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $b['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
