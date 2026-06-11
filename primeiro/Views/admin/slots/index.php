<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <!-- Legend -->
        <span class="badge rounded-pill px-3 py-2" style="background:#19875420;color:#198754;border:1px solid #19875440;">
            <i class="bi bi-circle-fill me-1" style="font-size:.6rem"></i> Disponível
        </span>
        <span class="badge rounded-pill px-3 py-2" style="background:#dc354520;color:#dc3545;border:1px solid #dc354540;">
            <i class="bi bi-circle-fill me-1" style="font-size:.6rem"></i> Agendado
        </span>
        <span class="badge rounded-pill px-3 py-2" style="background:#fd7e1420;color:#fd7e14;border:1px solid #fd7e1440;">
            <i class="bi bi-lock-fill me-1" style="font-size:.6rem"></i> Reservado (admin)
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= route_to('admin.slots.batch') ?>" class="btn btn-outline-primary">
            <i class="bi bi-calendar-plus me-1"></i> Criar em Lote
        </a>
        <a href="<?= route_to('admin.slots.new') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Novo Slot
        </a>
    </div>
</div>

<!-- Month navigation -->
<?php
    $prev = $month === 1 ? ['year' => $year - 1, 'month' => 12] : ['year' => $year, 'month' => $month - 1];
    $next = $month === 12 ? ['year' => $year + 1, 'month' => 1] : ['year' => $year, 'month' => $month + 1];
    $monthNames = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
?>
<div class="month-nav">
    <a href="?year=<?= $prev['year'] ?>&month=<?= $prev['month'] ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-chevron-left"></i>
    </a>
    <span class="month-label"><?= $monthNames[$month - 1] ?> <?= $year ?></span>
    <a href="?year=<?= $next['year'] ?>&month=<?= $next['month'] ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-chevron-right"></i>
    </a>
</div>

<!-- Calendar grid -->
<?php
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth = (int) date('t', $firstDay);
    $startDayOfWeek = (int) date('w', $firstDay); // 0=Sun

    $slotsByDate = [];
    foreach ($slots as $slot) {
        $slotsByDate[$slot['date']][] = $slot;
    }
?>

<div class="card p-3 mb-4">
    <div class="calendar-grid mb-1">
        <?php foreach (['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $day): ?>
            <div class="calendar-day-header"><?= $day ?></div>
        <?php endforeach; ?>
    </div>

    <div class="calendar-grid">
        <?php for ($i = 0; $i < $startDayOfWeek; $i++): ?>
            <div class="calendar-day other-month"></div>
        <?php endfor; ?>

        <?php for ($d = 1; $d <= $daysInMonth; $d++):
            $dateStr  = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $isToday  = $dateStr === date('Y-m-d');
            $daySlots = $slotsByDate[$dateStr] ?? [];
        ?>
            <div class="calendar-day <?= $isToday ? 'today' : '' ?>">
                <div class="calendar-day-number"><?= $d ?></div>
                <?php foreach ($daySlots as $slot):
                    $chipColor = match($slot['status']) {
                        'booked'    => ['bg' => '#dc354520', 'col' => '#dc3545'],
                        'held'      => ['bg' => '#fd7e1420', 'col' => '#fd7e14'],
                        'cancelled' => ['bg' => '#6c757d20', 'col' => '#6c757d'],
                        default     => ['bg' => $slot['color'].'20', 'col' => $slot['color']],
                    };
                    $lockIcon = $slot['status'] === 'held' ? '🔒 ' : '';
                ?>
                    <a href="<?= route_to('admin.slots.edit', $slot['id']) ?>"
                       class="slot-chip"
                       style="background:<?= $chipColor['bg'] ?>; color:<?= $chipColor['col'] ?>;"
                       title="<?= esc($slot['session_type_name']) ?> — <?= $slot['status'] ?>">
                        <?= $lockIcon ?><?= substr($slot['start_time'], 0, 5) ?> <?= esc($slot['session_type_name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Slot list table with hold/release actions -->
<?php if (! empty($slots)): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Lista de Slots — <?= $monthNames[$month - 1] ?> <?= $year ?></h6>
        <span class="badge bg-secondary"><?= count($slots) ?> slots</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slots as $slot): ?>
                <tr id="slot-row-<?= $slot['id'] ?>">
                    <td><?= date('d/m/Y (D)', strtotime($slot['date'])) ?></td>
                    <td><?= substr($slot['start_time'], 0, 5) ?> – <?= substr($slot['end_time'], 0, 5) ?></td>
                    <td>
                        <span class="color-dot" style="background:<?= esc($slot['color']) ?>"></span>
                        <?= esc($slot['session_type_name']) ?>
                    </td>
                    <td id="slot-status-<?= $slot['id'] ?>">
                        <?php match($slot['status']) {
                            'available' => print('<span class="badge bg-success">Disponível</span>'),
                            'booked'    => print('<span class="badge bg-danger">Agendado</span>'),
                            'held'      => print('<span class="badge" style="background:#fd7e14">🔒 Reservado (admin)</span>'),
                            'cancelled' => print('<span class="badge bg-secondary">Cancelado</span>'),
                            default     => print('<span class="badge bg-secondary">' . esc($slot['status']) . '</span>'),
                        }; ?>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1" id="slot-actions-<?= $slot['id'] ?>">
                            <?php if ($slot['status'] === 'available'): ?>
                                <button type="button" class="btn btn-sm btn-outline-warning slot-hold-btn"
                                        title="Reservar este slot"
                                        data-slot-id="<?= $slot['id'] ?>"
                                        data-url="<?= route_to('admin.slots.hold', $slot['id']) ?>">
                                    <i class="bi bi-lock-fill me-1"></i> Reservar
                                </button>
                            <?php elseif ($slot['status'] === 'held'): ?>
                                <button type="button" class="btn btn-sm btn-outline-success slot-release-btn"
                                        title="Liberar este slot"
                                        data-slot-id="<?= $slot['id'] ?>"
                                        data-url="<?= route_to('admin.slots.release', $slot['id']) ?>">
                                    <i class="bi bi-unlock-fill me-1"></i> Liberar
                                </button>
                            <?php endif; ?>

                            <a href="<?= route_to('admin.slots.edit', $slot['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form method="POST" action="<?= route_to('admin.slots.delete', $slot['id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        title="Remover slot"
                                        data-confirm="Remover este slot permanentemente?"
                                        data-icon="🗑️"
                                        data-color="btn-danger"
                                        data-confirm-label="Remover">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    // ─── Toast helper ───────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        let container = document.getElementById('slot-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'slot-toast-container';
            container.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' shadow py-2 px-3 mb-0 d-flex align-items-center gap-2';
        toast.style.cssText = 'min-width:220px;font-size:.875rem;animation:fadeInUp .2s ease;';
        toast.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + msg;
        container.appendChild(toast);
        setTimeout(() => toast.style.opacity = '0', 2500);
        setTimeout(() => toast.remove(), 2900);
    }

    // ─── AJAX Slot Toggle ────────────────────────────────────────────
    async function toggleSlot(btn, action) {
        const slotId = btn.dataset.slotId;
        const url    = btn.dataset.url;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: '',
            });

            const data = await resp.json();

            if (!data.success) {
                showToast(data.message || 'Erro ao atualizar slot.', 'error');
                btn.disabled = false;
                return;
            }

            const newStatus = data.status; // 'held' or 'available'

            // Update badge
            const statusCell = document.getElementById('slot-status-' + slotId);
            if (statusCell) {
                statusCell.innerHTML = newStatus === 'held'
                    ? '<span class="badge" style="background:#fd7e14">🔒 Reservado (admin)</span>'
                    : '<span class="badge bg-success">Disponível</span>';
            }

            // Swap button
            const actionsCell = document.getElementById('slot-actions-' + slotId);
            if (actionsCell) {
                // Find and replace just the hold/release button
                const existingBtn = actionsCell.querySelector('.slot-hold-btn, .slot-release-btn');
                if (existingBtn) {
                    if (newStatus === 'held') {
                        existingBtn.className = 'btn btn-sm btn-outline-success slot-release-btn';
                        existingBtn.title = 'Liberar este slot';
                        existingBtn.dataset.url = existingBtn.dataset.url.replace('/hold', '/release');
                        existingBtn.innerHTML = '<i class="bi bi-unlock-fill me-1"></i> Liberar';
                        existingBtn.onclick = null;
                        existingBtn.addEventListener('click', function() { toggleSlot(this, 'release'); }, { once: true });
                    } else {
                        existingBtn.className = 'btn btn-sm btn-outline-warning slot-hold-btn';
                        existingBtn.title = 'Reservar este slot';
                        existingBtn.dataset.url = existingBtn.dataset.url.replace('/release', '/hold');
                        existingBtn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Reservar';
                        existingBtn.onclick = null;
                        existingBtn.addEventListener('click', function() { toggleSlot(this, 'hold'); }, { once: true });
                    }
                    existingBtn.disabled = false;
                }
            }

            showToast(data.message);

        } catch (err) {
            showToast('Erro de rede. Tente novamente.', 'error');
            btn.disabled = false;
            btn.innerHTML = action === 'hold'
                ? '<i class="bi bi-lock-fill me-1"></i> Reservar'
                : '<i class="bi bi-unlock-fill me-1"></i> Liberar';
        }
    }

    // Bind all hold buttons
    document.querySelectorAll('.slot-hold-btn').forEach(btn => {
        btn.addEventListener('click', function() { toggleSlot(this, 'hold'); });
    });
    document.querySelectorAll('.slot-release-btn').forEach(btn => {
        btn.addEventListener('click', function() { toggleSlot(this, 'release'); });
    });
})();
</script>
<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<?= $this->endSection() ?>

