<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="card mx-auto" style="max-width:700px">
    <div class="card-header">
        <i class="bi bi-calendar-plus me-2 text-primary"></i>Criar Slots em Lote
    </div>
    <div class="card-body p-4">
        <p class="text-muted small mb-4">
            Crie vários slots automaticamente para um período. Informe os dias da semana e os horários desejados.
        </p>
        <form action="<?= route_to('admin.slots.batch.store') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Tipo de Sessão <span class="text-danger">*</span></label>
                <select name="session_type_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($sessionTypes as $type): ?>
                        <option value="<?= $type['id'] ?>">
                            <?= esc($type['name']) ?> (<?= $type['duration_minutes'] ?> min)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Data início <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required
                        value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data fim <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control" required
                        value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Dias da semana <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $weekDays = [0=>'Dom', 1=>'Seg', 2=>'Ter', 3=>'Qua', 4=>'Qui', 5=>'Sex', 6=>'Sáb'];
                    foreach ($weekDays as $num => $name): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="days_of_week[]"
                                value="<?= $num ?>" id="day<?= $num ?>"
                                <?= in_array($num, [1,2,3,4,5]) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="day<?= $num ?>"><?= $name ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Horários <span class="text-danger">*</span>
                    <small class="text-muted">(um por linha, ex: 09:00)</small>
                </label>
                <textarea name="times" class="form-control font-monospace" rows="5"
                    placeholder="09:00&#10;10:30&#10;14:00&#10;15:30">09:00
10:30
14:00
15:30</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label">Status inicial</label>
                <select name="status" class="form-select">
                    <option value="available" selected>Disponível</option>
                    <option value="blocked">Bloqueado</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= route_to('admin.slots') ?>" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-magic me-1"></i> Gerar Slots
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
