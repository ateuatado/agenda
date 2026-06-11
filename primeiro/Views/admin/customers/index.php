<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header"><i class="bi bi-people me-2 text-primary"></i>Clientes</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Cadastrado em</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td class="text-muted small">#<?= $c['id'] ?></td>
                            <td class="fw-500"><?= esc($c['name']) ?></td>
                            <td><?= esc($c['email']) ?></td>
                            <td><?= esc($c['phone'] ?? '—') ?></td>
                            <td class="text-muted small"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                            <td>
                                <a href="<?= route_to('admin.customers.show', $c['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
