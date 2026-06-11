<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="card mx-auto" style="max-width:600px">
    <div class="card-header">
        <i class="bi bi-camera me-2 text-primary"></i><?= esc($title) ?>
    </div>
    <div class="card-body p-4">
        <form action="<?= $sessionType ? route_to('admin.session_types.update', $sessionType['id']) : route_to('admin.session_types.create') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required maxlength="100"
                    placeholder="ex: Ensaio 1 hora"
                    value="<?= old('name', $sessionType['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Duração (minutos) <span class="text-danger">*</span></label>
                <input type="number" name="duration_minutes" class="form-control" required min="15" max="480" step="15"
                    value="<?= old('duration_minutes', $sessionType['duration_minutes'] ?? 60) ?>">
                <div class="form-text">Ex: 60 = 1 hora, 90 = 1h30, 120 = 2 horas</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="3"
                    placeholder="Descrição opcional..."><?= old('description', $sessionType['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Cor no calendário</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="color" class="form-control form-control-color"
                        value="<?= old('color', $sessionType['color'] ?? '#6366f1') ?>" style="width:60px;height:38px;">
                    <small class="text-muted">Exibida no calendário do admin.</small>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                        <?= old('active', $sessionType['active'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="active">Ativo (disponível para agendamento)</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= route_to('admin.session_types') ?>" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> <?= $sessionType ? 'Atualizar' : 'Criar' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
