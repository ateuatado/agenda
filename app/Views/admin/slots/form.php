<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="card mx-auto" style="max-width:600px">
    <div class="card-header">
        <i class="bi bi-<?= $slot ? 'pencil' : 'plus-circle' ?> me-2 text-primary"></i>
        <?= esc($title) ?>
    </div>
    <div class="card-body p-4">
        <form action="<?= $slot ? route_to('admin.slots.update', $slot['id']) : route_to('admin.slots.create') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Tipo de Sessão <span class="text-danger">*</span></label>
                <select name="session_type_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($sessionTypes as $type): ?>
                        <option value="<?= $type['id'] ?>"
                            <?= old('session_type_id', $slot['session_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                            <?= esc($type['name']) ?> (<?= $type['duration_minutes'] ?>min)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Data <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" required
                        value="<?= old('date', $slot['date'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hora de início <span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control" required
                        value="<?= old('start_time', substr($slot['start_time'] ?? '', 0, 5)) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <?php foreach ([
                        'available' => 'Disponível',
                        'booked'    => 'Agendado',
                        'held'      => 'Reservado (admin)',
                        'cancelled' => 'Cancelado',
                    ] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= old('status', $slot['status'] ?? 'available') === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text text-muted">
                    Use "Reservado (admin)" para bloquear o slot sem registrar um cliente.
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Observações internas</label>
                <textarea name="notes" class="form-control" rows="3"
                    placeholder="Anotações visíveis apenas para o admin..."><?= old('notes', $slot['notes'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= route_to('admin.slots') ?>" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> <?= $slot ? 'Atualizar' : 'Criar Slot' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
