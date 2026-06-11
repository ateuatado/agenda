<?= $this->extend('client/layouts/main') ?>

<?= $this->section('hero') ?>
<div class="client-hero">
    <h1>Agende seu Ensaio</h1>
    <p class="subtitle">Escolha a data ideal para o seu ensaio fotográfico.</p>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- All slots as JSON for JavaScript interactivity (no extra fetch needed) -->
<script>
const AGENDA_SLOTS = <?= json_encode($slots, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const BOOKING_BASE_URL = '<?= base_url('agendar') ?>';
const INTEREST_BASE_URL = '<?= base_url('interesse') ?>';
</script>

<!-- Month Navigation -->
<div class="cal-header">
    <div class="cal-nav">
        <?php if ($prev): ?>
            <a href="?year=<?= $prev['year'] ?>&month=<?= $prev['month'] ?>" class="cal-nav-btn" id="btn-prev" title="Mês anterior">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
        <?php else: ?>
            <span class="cal-nav-btn disabled"></span>
        <?php endif; ?>

        <div class="cal-month-label">
            <span class="cal-month-name"><?= esc($monthName) ?></span>
            <span class="cal-year"><?= $year ?></span>
        </div>

        <a href="?year=<?= $next['year'] ?>&month=<?= $next['month'] ?>" class="cal-nav-btn" id="btn-next" title="Próximo mês">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>

    <!-- Summary badges -->
    <?php
        $totalAvailable = 0;
        $totalBooked    = 0;
        foreach ($slots as $s) {
            if ($s['status'] === 'available') $totalAvailable++;
            elseif ($s['status'] === 'booked')   $totalBooked++;
        }
    ?>
    <div class="cal-summary">
        <span class="cal-badge available"><span class="cal-badge-dot"></span><?= $totalAvailable ?> disponíve<?= $totalAvailable === 1 ? 'l' : 'is' ?></span>
        <?php if ($totalAvailable > 0 && $totalAvailable <= 5): ?>
            <span class="cal-badge scarcity">🔥 Últimas vagas!</span>
        <?php endif; ?>
    </div>
</div>

<!-- Calendar Grid -->
<div class="cal-grid-wrapper">
    <!-- Day of week headers -->
    <div class="cal-grid-header">
        <div>Dom</div>
        <div>Seg</div>
        <div>Ter</div>
        <div>Qua</div>
        <div>Qui</div>
        <div>Sex</div>
        <div>Sáb</div>
    </div>

    <!-- Calendar cells -->
    <div class="cal-grid" id="cal-grid">
        <?php foreach ($calendar as $week): ?>
            <?php foreach ($week as $cell): ?>
                <?php if ($cell === null): ?>
                    <div class="cal-cell empty"></div>
                <?php else: ?>
                    <?php
                        $cls = 'cal-cell';
                        if ($cell['isPast'])         $cls .= ' past';
                        elseif ($cell['isToday'])    $cls .= ' today';
                        elseif ($cell['available'] > 0) $cls .= ' has-slots';
                        elseif ($cell['total'] > 0)  $cls .= ' full';
                        // else: no-slot day, neutral
                    ?>
                    <div class="<?= $cls ?>"
                         data-date="<?= esc($cell['date']) ?>"
                         <?php if (!$cell['isPast'] && $cell['total'] > 0): ?>
                         onclick="selectDay('<?= esc($cell['date']) ?>', this)"
                         <?php endif; ?>>
                        <div class="cal-day-number"><?= $cell['day'] ?></div>
                        <?php if (!$cell['isPast']): ?>
                            <?php if ($cell['available'] > 0): ?>
                                <div class="cal-day-indicator">
                                    <span class="cal-dot dot-available"></span>
                                    <span class="cal-day-count"><?= $cell['available'] ?></span>
                                </div>
                                <?php if ($cell['available'] <= 2): ?>
                                    <div class="cal-day-scarcity">Apenas <?= $cell['available'] ?>!</div>
                                <?php endif; ?>
                            <?php elseif ($cell['total'] > 0): ?>
                                <div class="cal-day-indicator">
                                    <span class="cal-dot dot-full"></span>
                                    <span class="cal-day-count cal-day-count-full">Lotado</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($cell['isToday']): ?>
                                <div class="cal-today-label">Hoje</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- Legend -->
<div class="cal-legend">
    <span class="legend-item"><span class="cal-dot dot-available"></span> Disponível</span>
    <span class="legend-item"><span class="cal-dot dot-full"></span> Lotado</span>
    <span class="legend-item"><span class="cal-dot dot-none"></span> Sem sessão</span>
</div>

<!-- Day Detail Panel (hidden until day is selected) -->
<div class="cal-detail-panel" id="cal-detail" style="display:none;">
    <div class="cal-detail-header">
        <div class="cal-detail-date" id="detail-date-label"></div>
        <button class="cal-detail-close" onclick="closeDetail()" title="Fechar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="cal-detail-slots" id="detail-slots"></div>
</div>

<!-- Interest Modal -->
<div class="cal-modal-overlay" id="interest-modal" style="display:none;" onclick="closeInterestModal(event)">
    <div class="cal-modal">
        <div class="cal-modal-header">
            <h3>Entrar na lista de espera</h3>
            <button class="cal-detail-close" onclick="closeInterestModal()" title="Fechar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <p class="cal-modal-desc">Se o horário abrir vaga, você será notificado por e-mail.</p>
        <form id="interest-form" method="POST">
            <?= csrf_field() ?>
            <div class="form-group-cal">
                <label for="interest-name">Seu nome</label>
                <input type="text" name="name" id="interest-name" class="cal-input" required placeholder="Nome completo">
            </div>
            <div class="form-group-cal">
                <label for="interest-email">E-mail</label>
                <input type="email" name="email" id="interest-email" class="cal-input" required placeholder="seu@email.com">
            </div>
            <div class="form-group-cal">
                <label for="interest-phone">WhatsApp (opcional)</label>
                <input type="tel" name="phone" id="interest-phone" class="cal-input" placeholder="(11) 99999-9999">
            </div>
            <button type="submit" class="btn-cal-primary">Quero ser avisado</button>
        </form>
    </div>
</div>

<script>
// ---- Helpers ---------------------------------------------------------------

const MONTH_NAMES = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                     'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
const DAY_NAMES   = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira',
                     'Quinta-feira','Sexta-feira','Sábado'];

function formatDateBR(dateStr) {
    const [y, m, d] = dateStr.split('-').map(Number);
    const ts  = new Date(y, m - 1, d);
    const dow = ts.getDay();
    return DAY_NAMES[dow] + ', ' + d + ' de ' + MONTH_NAMES[m - 1];
}

function formatDuration(minutes) {
    if (minutes < 60) return minutes + ' min';
    const h = Math.floor(minutes / 60);
    const r = minutes % 60;
    return h + 'h' + (r > 0 ? r + 'min' : '');
}

// ---- Calendar day selection -------------------------------------------------

let currentSelectedCell = null;

function selectDay(date, cellEl) {
    // Deselect previous
    if (currentSelectedCell) {
        currentSelectedCell.classList.remove('selected');
    }
    cellEl.classList.add('selected');
    currentSelectedCell = cellEl;

    // Filter slots for this date
    const daySlots = AGENDA_SLOTS.filter(s => s.date === date);
    renderDetailPanel(date, daySlots);
}

function closeDetail() {
    document.getElementById('cal-detail').style.display = 'none';
    document.getElementById('cal-detail').classList.remove('visible');
    if (currentSelectedCell) {
        currentSelectedCell.classList.remove('selected');
        currentSelectedCell = null;
    }
}

function renderDetailPanel(date, slots) {
    const panel      = document.getElementById('cal-detail');
    const dateLabel  = document.getElementById('detail-date-label');
    const slotsDiv   = document.getElementById('detail-slots');

    dateLabel.textContent = formatDateBR(date);

    if (slots.length === 0) {
        slotsDiv.innerHTML = '<p class="detail-empty">Nenhum horário para esta data.</p>';
    } else {
        slotsDiv.innerHTML = slots.map(slot => renderSlotCard(slot)).join('');
    }

    // Show panel
    panel.style.display = 'block';
    setTimeout(() => panel.classList.add('visible'), 10);

    // Scroll to panel
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function renderSlotCard(slot) {
    const time     = slot.start_time.substring(0, 5);
    const duration = formatDuration(parseInt(slot.duration_minutes));
    const color    = slot.color || '#6366f1';
    const isAvail  = slot.status === 'available';

    if (isAvail) {
        return `
        <a href="${BOOKING_BASE_URL}/${slot.id}" class="slot-detail-card available">
            <div class="sdc-color-bar" style="background:${color}"></div>
            <div class="sdc-body">
                <div class="sdc-time">${time}</div>
                <div class="sdc-type">${escHtml(slot.session_type_name)}</div>
                <div class="sdc-meta">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    ${duration}
                </div>
            </div>
            <div class="sdc-action">
                <span class="sdc-btn-book">Agendar →</span>
            </div>
        </a>`;
    } else {
        return `
        <div class="slot-detail-card occupied" data-slot-id="${slot.id}">
            <div class="sdc-color-bar" style="background:#94a3b8"></div>
            <div class="sdc-body">
                <div class="sdc-time">${time}</div>
                <div class="sdc-type">${escHtml(slot.session_type_name)}</div>
                <div class="sdc-meta">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    ${duration}
                </div>
            </div>
            <div class="sdc-action">
                <button class="sdc-btn-interest" onclick="openInterestModal(${slot.id})">Avise-me</button>
            </div>
        </div>`;
    }
}

function escHtml(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

// ---- Interest modal --------------------------------------------------------

function openInterestModal(slotId) {
    const form = document.getElementById('interest-form');
    form.action = INTEREST_BASE_URL + '/' + slotId;
    document.getElementById('interest-modal').style.display = 'flex';
    document.getElementById('interest-name').focus();
}

function closeInterestModal(event) {
    if (!event || event.target === document.getElementById('interest-modal')) {
        document.getElementById('interest-modal').style.display = 'none';
    }
}

// Keyboard: Escape closes
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetail();
        closeInterestModal();
    }
});
</script>

<?= $this->endSection() ?>
