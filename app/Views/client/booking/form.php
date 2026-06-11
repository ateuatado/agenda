<?= $this->extend('client/layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
<div class="col-md-6">

<div class="card mb-3">
    <div class="card-body p-4">
        <h5 class="fw-700 mb-1">Detalhes do Ensaio</h5>
        <div class="d-flex align-items-center gap-2 mt-3 mb-1">
            <span class="color-dot" style="background:<?= esc($slot['color']) ?>"></span>
            <strong><?= esc($slot['session_type_name']) ?></strong>
        </div>
        <p class="text-muted mb-1">
            <i class="bi bi-calendar3 me-1"></i>
            <?= date('l, d \d\e F \d\e Y', strtotime($slot['date'])) ?>
        </p>
        <p class="text-muted mb-1">
            <i class="bi bi-clock me-1"></i>
            <?= substr($slot['start_time'], 0, 5) ?> às <?= substr($slot['end_time'], 0, 5) ?>
        </p>
        <p class="text-muted mb-0">
            <i class="bi bi-hourglass me-1"></i>
            <?= \App\Models\SessionTypeModel::formatDuration($slot['duration_minutes']) ?>
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header">Seus dados</div>
    <div class="card-body p-4">
        <form action="<?= route_to('client.book.store', $slot['id']) ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Nome completo <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="Seu nome"
                    value="<?= old('name') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required placeholder="seu@email.com"
                    value="<?= old('email') ?>">
                <div class="form-text">Enviaremos a confirmação para este e-mail.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">WhatsApp / Telefone <span class="text-danger">*</span></label>
                <input type="tel" name="phone" class="form-control" required placeholder="(11) 99999-9999"
                    value="<?= old('phone') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Observações <span class="text-muted">(opcional)</span></label>
                <textarea name="notes" class="form-control" rows="3"
                    placeholder="Conte um pouco sobre o ensaio que você deseja..."><?= old('notes') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= route_to('client.home') ?>" class="btn btn-outline-secondary">Voltar</a>
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-calendar-check me-1"></i> Confirmar Agendamento
                </button>
            </div>
        </form>
    </div>
</div>

</div>
</div>

<?= $this->endSection() ?>
