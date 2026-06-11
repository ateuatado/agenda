<?php
/**
 * Widget Calendar — HTML puro sem layout CI4.
 * Pode ser embutido em qualquer página via fetch + innerHTML.
 *
 * Todas as classes usam prefixo "aw-" (agenda-widget) para evitar conflito
 * com o CSS do site host.
 */
?>
<link rel="stylesheet" href="<?= esc($widgetCssUrl) ?>">

<div class="aw-root" data-theme="<?= esc($theme) ?>" data-year="<?= $year ?>" data-month="<?= $month ?>">

    <!-- Navigation -->
    <div class="aw-nav">
        <?php if ($prev): ?>
            <button class="aw-nav-btn" id="aw-prev"
                    onclick="awLoadMonth(<?= $prev['year'] ?>, <?= $prev['month'] ?>)"
                    title="Mês anterior">&#8592;</button>
        <?php else: ?>
            <span class="aw-nav-btn aw-disabled"></span>
        <?php endif; ?>

        <div class="aw-month-label">
            <strong class="aw-month-name"><?= esc($monthName) ?></strong>
            <span class="aw-year"><?= $year ?></span>
        </div>

        <button class="aw-nav-btn" id="aw-next"
                onclick="awLoadMonth(<?= $next['year'] ?>, <?= $next['month'] ?>)"
                title="Próximo mês">&#8594;</button>
    </div>

    <?php
        $totalAvailable = count(array_filter($slots, fn($s) => $s['status'] === 'available'));
    ?>
    <?php if ($totalAvailable > 0 && $totalAvailable <= 5): ?>
        <div class="aw-scarcity">🔥 Últimas vagas — <?= $totalAvailable ?> horário<?= $totalAvailable > 1 ? 's' : '' ?> disponíve<?= $totalAvailable === 1 ? 'l' : 'is' ?></div>
    <?php endif; ?>

    <!-- Calendar Grid -->
    <div class="aw-grid-header">
        <span>Dom</span><span>Seg</span><span>Ter</span>
        <span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span>
    </div>

    <div class="aw-grid" id="aw-grid">
        <?php foreach ($calendar as $week): ?>
            <?php foreach ($week as $cell): ?>
                <?php if ($cell === null): ?>
                    <div class="aw-cell aw-empty"></div>
                <?php else: ?>
                    <?php
                        $cls = 'aw-cell';
                        if ($cell['isPast'])              $cls .= ' aw-past';
                        elseif ($cell['isToday'])         $cls .= ' aw-today';
                        elseif ($cell['available'] > 0)  $cls .= ' aw-has-slots';
                        elseif ($cell['total'] > 0)      $cls .= ' aw-full';
                        $clickable = !$cell['isPast'] && $cell['total'] > 0;
                    ?>
                    <div class="<?= $cls ?>"
                         <?php if ($clickable): ?>
                         onclick="awSelectDay('<?= esc($cell['date']) ?>', this)"
                         data-date="<?= esc($cell['date']) ?>"
                         <?php endif; ?>>
                        <div class="aw-day-num"><?= $cell['day'] ?></div>
                        <?php if (!$cell['isPast'] && $cell['available'] > 0): ?>
                            <div class="aw-dot aw-dot-avail"></div>
                            <?php if ($cell['available'] <= 2): ?>
                                <div class="aw-scarce"><?= $cell['available'] ?>!</div>
                            <?php endif; ?>
                        <?php elseif (!$cell['isPast'] && $cell['total'] > 0): ?>
                            <div class="aw-dot aw-dot-full"></div>
                        <?php endif; ?>
                        <?php if ($cell['isToday']): ?>
                            <div class="aw-today-label">Hoje</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- Day detail panel -->
    <div class="aw-detail" id="aw-detail" style="display:none;">
        <div class="aw-detail-header">
            <span class="aw-detail-date" id="aw-detail-date"></span>
            <button class="aw-close-btn" onclick="awCloseDetail()">&#10005;</button>
        </div>
        <div class="aw-detail-slots" id="aw-detail-slots"></div>
    </div>

    <!-- Legend -->
    <div class="aw-legend">
        <span><span class="aw-dot aw-dot-avail"></span> Disponível</span>
        <span><span class="aw-dot aw-dot-full"></span> Lotado</span>
    </div>
</div>

<script>
(function() {
    // Slots data embedded from PHP
    var AW_SLOTS   = <?= json_encode($slots, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var AW_BASE    = <?= json_encode(rtrim($base, '/')) ?>;
    var AW_WIDGET  = <?= json_encode(rtrim(base_url('widget/calendar'), '/')) ?>;
    var AW_SELECTED = null;

    var MONTHS = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                  'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    var DAYS   = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira',
                  'Quinta-feira','Sexta-feira','Sábado'];

    function formatDate(d) {
        var parts = d.split('-');
        var y = parseInt(parts[0]), m = parseInt(parts[1]), day = parseInt(parts[2]);
        var ts = new Date(y, m - 1, day);
        return DAYS[ts.getDay()] + ', ' + day + ' de ' + MONTHS[m - 1];
    }

    function formatDur(min) {
        if (min < 60) return min + ' min';
        var h = Math.floor(min / 60), r = min % 60;
        return h + 'h' + (r ? r + 'min' : '');
    }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    // Expose globally so onclick attributes work
    window.awSelectDay = function(date, el) {
        if (AW_SELECTED) AW_SELECTED.classList.remove('aw-selected');
        el.classList.add('aw-selected');
        AW_SELECTED = el;

        var daySlots = AW_SLOTS.filter(function(s){ return s.date === date; });
        var detail   = document.getElementById('aw-detail');
        var dateEl   = document.getElementById('aw-detail-date');
        var slotsEl  = document.getElementById('aw-detail-slots');

        dateEl.textContent = formatDate(date);
        slotsEl.innerHTML  = daySlots.length ? daySlots.map(renderSlot).join('') : '<p class="aw-empty-msg">Nenhum horário nesta data.</p>';

        detail.style.display = 'block';
        setTimeout(function(){ detail.classList.add('aw-visible'); }, 10);
    };

    window.awCloseDetail = function() {
        var detail = document.getElementById('aw-detail');
        detail.classList.remove('aw-visible');
        setTimeout(function(){ detail.style.display = 'none'; }, 250);
        if (AW_SELECTED) { AW_SELECTED.classList.remove('aw-selected'); AW_SELECTED = null; }
    };

    window.awLoadMonth = function(year, month) {
        var root = document.querySelector('.aw-root');
        if (!root) return;
        var container = root.parentElement;
        var theme = root.getAttribute('data-theme') || 'light';
        var url   = AW_WIDGET + '?year=' + year + '&month=' + month +
                    '&base=' + encodeURIComponent(AW_BASE) + '&theme=' + theme;
        container.style.opacity = '0.5';
        fetch(url)
            .then(function(r){ return r.text(); })
            .then(function(html){
                container.innerHTML = html;
                container.style.opacity = '1';
                // Re-run scripts — simple approach: evaluate inline scripts
                Array.from(container.querySelectorAll('script')).forEach(function(old){
                    var s = document.createElement('script');
                    s.textContent = old.textContent;
                    old.parentNode.replaceChild(s, old);
                });
            })
            .catch(function(){ container.style.opacity = '1'; });
    };

    function renderSlot(slot) {
        var time  = slot.start_time.substring(0, 5);
        var dur   = formatDur(parseInt(slot.duration_minutes));
        var color = slot.color || '#6366f1';
        var avail = slot.status === 'available';

        if (avail) {
            return '<a href="' + AW_BASE + '/' + slot.id + '" class="aw-slot-card aw-slot-avail">' +
                   '<span class="aw-slot-bar" style="background:' + color + '"></span>' +
                   '<span class="aw-slot-time">' + time + '</span>' +
                   '<span class="aw-slot-info"><span class="aw-slot-type">' + esc(slot.session_type_name) + '</span>' +
                   '<span class="aw-slot-dur">⏱ ' + dur + '</span></span>' +
                   '<span class="aw-slot-cta">Agendar →</span></a>';
        } else {
            return '<div class="aw-slot-card aw-slot-full">' +
                   '<span class="aw-slot-bar" style="background:#94a3b8"></span>' +
                   '<span class="aw-slot-time">' + time + '</span>' +
                   '<span class="aw-slot-info"><span class="aw-slot-type">' + esc(slot.session_type_name) + '</span>' +
                   '<span class="aw-slot-dur">⏱ ' + dur + '</span></span>' +
                   '<span class="aw-slot-status">Lotado</span></div>';
        }
    }
})();
</script>
