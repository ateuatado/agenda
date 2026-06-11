<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= route_to('admin.session_types.new') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo Tipo
    </a>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-camera me-2 text-primary"></i>Tipos de Sessão</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>Nome</th><th>Duração</th><th>Cor</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (empty($sessionTypes)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum tipo cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($sessionTypes as $type): ?>
                        <tr>
                            <td class="fw-500"><?= esc($type['name']) ?></td>
                            <td><?= \App\Models\SessionTypeModel::formatDuration($type['duration_minutes']) ?></td>
                            <td>
                                <span class="color-dot" style="background:<?= esc($type['color']) ?>"></span>
                                <code class="text-muted small"><?= esc($type['color']) ?></code>
                            </td>
                            <td>
                                <span class="badge <?= $type['active'] ? 'badge-confirmed' : 'badge-cancelled' ?> rounded-pill px-2">
                                    <?= $type['active'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= route_to('admin.session_types.edit', $type['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?= route_to('admin.session_types.delete', $type['id']) ?>" method="POST" class="d-inline"
                                    onsubmit="return confirm('Remover este tipo de sessão?')">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
