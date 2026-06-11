<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin') ?> — Agenda MarcoSantoFoto</title>
    <meta name="description" content="Painel de administração da agenda do Studio MarcoSantoFoto.">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
</head>
<body>

<div class="admin-wrapper">

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= route_to('admin.dashboard') ?>" class="sidebar-brand">
            <div class="brand-icon">📸</div>
            <div>
                <div class="brand-text">MarcoSantoFoto</div>
                <div class="brand-sub">Painel Admin</div>
            </div>
        </a>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Principal</div>
            <a href="<?= route_to('admin.dashboard') ?>" class="nav-link <?= uri_string() === 'admin' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?= route_to('admin.slots') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/slots') ? 'active' : '' ?>">
                <i class="bi bi-calendar3"></i> Agenda / Slots
            </a>
            <a href="<?= route_to('admin.slots.batch') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/slots/batch') ? 'active' : '' ?>">
                <i class="bi bi-calendar-plus"></i> Criar Slots em Lote
            </a>

            <div class="sidebar-section-label">Cadastros</div>
            <a href="<?= route_to('admin.session_types') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/session-types') ? 'active' : '' ?>">
                <i class="bi bi-camera"></i> Tipos de Sessão
            </a>
            <a href="<?= route_to('admin.bookings') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/bookings') ? 'active' : '' ?>">
                <i class="bi bi-journal-check"></i> Agendamentos
            </a>
            <a href="<?= route_to('admin.customers') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/customers') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Clientes
            </a>

            <div class="sidebar-section-label">Sistema</div>
            <a href="<?= route_to('admin.settings') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/settings') ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Configurações
            </a>
            <a href="<?= route_to('admin.api_docs') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/api-docs') ? 'active' : '' ?>">
                <i class="bi bi-code-slash"></i> API Docs
            </a>
            <a href="<?= base_url('/') ?>" class="nav-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Ver Site Público
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-person-circle fs-5"></i>
                <div>
                    <div class="text-light fw-500" style="font-size:.8rem"><?= esc(auth()->user()->username) ?></div>
                    <div style="font-size:.7rem">Administrador</div>
                </div>
            </div>
            <a href="<?= route_to('logout') ?>" class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-box-arrow-left me-1"></i> Sair
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h1 class="page-title"><?= esc($title ?? 'Admin') ?></h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small d-none d-md-inline">
                    <?= date('d/m/Y, H:i') ?>
                </span>
            </div>
        </header>

        <main class="admin-content">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <strong><i class="bi bi-exclamation-triangle me-2"></i>Erros de validação:</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>

        </main>
    </div>
</div>

<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

<!-- Modal de Confirmação Global -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div style="font-size:2rem; margin-bottom:.5rem" id="confirmIcon">⚠️</div>
                <p class="mb-0" id="confirmMessage">Tem certeza?</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm" id="confirmBtn">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Sidebar toggle for mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.getElementById('adminSidebar').classList.toggle('open');
    });

    // Global confirm modal — substitui window.confirm() para forms com data-confirm
    (function() {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        let pendingForm = null;

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-confirm]');
            if (!btn) return;

            const form = btn.closest('form');
            if (!form) return;

            e.preventDefault();
            e.stopPropagation();

            pendingForm = form;
            const msg   = btn.dataset.confirm || 'Tem certeza?';
            const icon  = btn.dataset.icon    || '⚠️';
            const color = btn.dataset.color   || 'btn-danger';

            document.getElementById('confirmMessage').textContent = msg;
            document.getElementById('confirmIcon').textContent    = icon;

            const confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.className = 'btn btn-sm ' + color;
            confirmBtn.textContent = btn.dataset.confirmLabel || 'Confirmar';

            modal.show();
        });

        document.getElementById('confirmBtn').addEventListener('click', function() {
            modal.hide();
            if (pendingForm) {
                pendingForm.submit();
                pendingForm = null;
            }
        });
    })();
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>

