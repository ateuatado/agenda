<?= $this->extend('client/layouts/main') ?>

<?= $this->section('hero') ?>
<div class="client-hero">
    <div class="container">
        <h1>📅 Minha Agenda</h1>
        <p class="subtitle">Seus agendamentos no Studio MarcoSantoFoto.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h5 class="fw-700 mb-3">Meus Agendamentos</h5>

<?php if (empty($bookings)): ?>
    <div class="text-center py-4">
        <div style="font-size:2.5rem">📭</div>
        <p class="text-muted mt-2">Você ainda não tem agendamentos.</p>
        <a href="<?= route_to('client.home') ?>" class="btn btn-primary">Ver horários disponíveis</a>
    </div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <?php foreach ($bookings as $b): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="color-dot" style="background:<?= esc($b['color']) ?>"></span>
                                <strong><?= esc($b['session_type_name']) ?></strong>
                            </div>
                            <span class="badge badge-<?= $b['status'] ?> rounded-pill"><?= ucfirst($b['status']) ?></span>
                        </div>
                        <p class="mb-1 text-muted">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= date('d/m/Y', strtotime($b['date'])) ?>
                        </p>
                        <p class="mb-3 text-muted">
                            <i class="bi bi-clock me-1"></i>
                            <?= substr($b['start_time'], 0, 5) ?> às <?= substr($b['end_time'], 0, 5) ?>
                        </p>
                        <?php if ($b['status'] !== 'cancelled' && strtotime($b['date']) > time()): ?>
                            <form action="<?= route_to('client.cancel', $b['id']) ?>" method="POST"
                                onsubmit="return confirm('Deseja cancelar este agendamento?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (! empty($interests)): ?>
<h5 class="fw-700 mb-3 mt-4">Meus Interesses (Lista de Espera)</h5>
<div class="row g-3">
    <?php foreach ($interests as $i): ?>
        <div class="col-md-6">
            <div class="card" style="border:2px solid #fcd34d20">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <strong><?= esc($i['session_type_name']) ?></strong>
                        <span class="badge badge-interested rounded-pill">Interesse</span>
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        <?= date('d/m/Y', strtotime($i['date'])) ?> às <?= substr($i['start_time'], 0, 5) ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="mt-4">
    <a href="<?= route_to('client.home') ?>" class="btn btn-outline-primary">
        <i class="bi bi-plus-circle me-1"></i> Agendar outro ensaio
    </a>
    <a href="<?= route_to('client.logout') ?>" class="btn btn-outline-secondary ms-2">
        <i class="bi bi-box-arrow-left me-1"></i> Sair
    </a>
</div>

<?= $this->endSection() ?>
