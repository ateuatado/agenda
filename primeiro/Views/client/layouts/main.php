<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Agenda') ?> — Studio MarcoSantoFoto</title>
    <meta name="description" content="Agende seu ensaio fotográfico no Studio MarcoSantoFoto.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <style>
    :root {
        --gold:      #C8A96E;
        --gold-dim:  #a08855;
        --bg:        #000000;
        --bg-card:   #0d0d0d;
        --bg-card2:  #111111;
        --border:    #1f1f1f;
        --border-lt: #2a2a2a;
        --text:      #f5f0e8;
        --text-muted:#7a7470;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
    }

    a { color: var(--gold); text-decoration: none; }
    a:hover { color: #fff; }

    /* ── Navbar ─── */
    .site-nav {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 100;
        padding: 1.5rem 3rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(to bottom, rgba(0,0,0,.92) 0%, rgba(0,0,0,0) 100%);
    }

    .site-nav .brand {
        font-size: .68rem;
        font-weight: 600;
        letter-spacing: .22em;
        text-transform: uppercase;
        color: var(--text);
        text-decoration: none;
        transition: color .2s;
    }
    .site-nav .brand:hover { color: var(--gold); }

    .site-nav .nav-links { display: flex; align-items: center; gap: 2rem; }
    .site-nav .nav-links a {
        font-size: .63rem;
        font-weight: 600;
        letter-spacing: .2em;
        text-transform: uppercase;
        color: var(--text);
        transition: color .2s;
    }
    .site-nav .nav-links a:hover { color: var(--gold); }

    /* ── Hero ─── */
    .client-hero {
        min-height: 38vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 8rem 1.5rem 3rem;
        position: relative;
    }
    .client-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at center, rgba(200,169,110,.05) 0%, transparent 70%);
        pointer-events: none;
    }
    .client-hero h1 {
        font-family: 'EB Garamond', Georgia, serif;
        font-size: clamp(2rem, 5vw, 3.6rem);
        font-weight: 400;
        color: var(--gold);
        line-height: 1.15;
        margin-bottom: .6rem;
    }
    .client-hero .subtitle {
        font-family: 'EB Garamond', Georgia, serif;
        font-size: clamp(.95rem, 2vw, 1.15rem);
        font-style: italic;
        color: rgba(245,240,232,.45);
        max-width: 440px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* ── Main ─── */
    .site-main { padding: 0 0 6rem; }
    .site-container { max-width: 960px; margin: 0 auto; padding: 0 1.5rem; }

    /* ── Month nav ─── */
    .month-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2rem;
        padding: 2.5rem 0;
        border-bottom: 1px solid var(--border);
        margin-bottom: 3rem;
    }
    .month-nav .month-label {
        font-size: .68rem;
        font-weight: 600;
        letter-spacing: .24em;
        text-transform: uppercase;
        color: var(--text);
        min-width: 180px;
        text-align: center;
    }
    .month-nav .nav-arrow {
        width: 34px; height: 34px;
        border: 1px solid var(--border-lt);
        display: grid;
        place-items: center;
        color: var(--text-muted);
        transition: all .2s;
        text-decoration: none;
        font-size: .85rem;
    }
    .month-nav .nav-arrow:hover { border-color: var(--gold); color: var(--gold); }

    /* ── Day group ─── */
    .day-group { margin-bottom: 3.5rem; }
    .day-label {
        font-size: .6rem;
        font-weight: 600;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--text-muted);
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
        margin-bottom: 1.5rem;
    }

    /* ── Slot grid ─── */
    .slot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(195px, 1fr));
        gap: 1px;
        background: var(--border);
    }

    /* Células fantasma para preencher linha incompleta */
    .slot-grid::after {
        content: '';
        background: var(--bg);
    }

    .slot-card {
        background: var(--bg-card);
        padding: 1.5rem 1.25rem;
        display: block;
        text-decoration: none;
        transition: background .2s;
        position: relative;
        overflow: hidden;
    }
    .slot-card.available:hover { background: #0f0f0f; }
    .slot-card.available::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 1px;
        background: var(--gold);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform .28s ease;
    }
    .slot-card.available:hover::after { transform: scaleX(1); }
    .slot-card.occupied { cursor: default; }

    .slot-time {
        font-family: 'EB Garamond', Georgia, serif;
        font-size: 2.1rem;
        font-weight: 400;
        color: var(--gold);
        line-height: 1;
        margin-bottom: .4rem;
    }
    .slot-card.occupied .slot-time { color: #252525; text-decoration: line-through; }

    .slot-type-name {
        font-size: .65rem;
        font-weight: 500;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: .5rem;
    }
    .slot-card.occupied .slot-type-name { color: #1e1e1e; }

    .slot-duration {
        font-size: .68rem;
        color: #3a3a3a;
        margin-bottom: 1.1rem;
    }

    .slot-status-tag {
        font-size: .58rem;
        font-weight: 600;
        letter-spacing: .14em;
        text-transform: uppercase;
        padding: .28rem .65rem;
        display: inline-block;
    }
    .slot-status-tag.available { color: var(--gold); border: 1px solid rgba(200,169,110,.25); }
    .slot-status-tag.occupied  { color: #2a2a2a; border: 1px solid #181818; }

    /* ── Empty state ─── */
    .empty-state { text-align: center; padding: 6rem 1.5rem; }
    .empty-state .empty-icon {
        font-size: 3rem;
        color: #1a1a1a;
        margin-bottom: 1.5rem;
        line-height: 1;
    }
    .empty-state h3 {
        font-family: 'EB Garamond', Georgia, serif;
        font-size: 1.6rem;
        font-weight: 400;
        color: #2a2a2a;
        margin-bottom: .5rem;
    }
    .empty-state p {
        font-size: .65rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #222;
    }

    /* ── Alerts ─── */
    .site-alert {
        border: 1px solid var(--border-lt);
        background: var(--bg-card);
        padding: .85rem 1.25rem;
        font-size: .75rem;
        letter-spacing: .04em;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .site-alert.success { border-color: rgba(74,124,89,.4); color: #5a9a6a; }
    .site-alert.danger  { border-color: rgba(204,68,68,.35); color: #c06060; }
    .site-alert.warning { border-color: rgba(200,169,110,.3); color: var(--gold); }
    .site-alert .close-btn {
        margin-left: auto; background: none; border: none;
        color: inherit; opacity: .4; cursor: pointer; font-size: 1rem; padding: 0;
    }
    .site-alert .close-btn:hover { opacity: 1; }

    /* ── Footer ─── */
    .site-footer {
        border-top: 1px solid var(--border);
        padding: 2.5rem 3rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .site-footer .footer-brand {
        font-size: .6rem; font-weight: 600;
        letter-spacing: .22em; text-transform: uppercase; color: var(--text-muted);
    }
    .site-footer .footer-copy {
        font-size: .6rem; letter-spacing: .1em; color: #222;
    }

    /* ── Responsive ─── */
    @media (max-width: 640px) {
        .site-nav { padding: 1.25rem 1.25rem; }
        .site-container { padding: 0 1rem; }
        .slot-grid { grid-template-columns: 1fr 1fr; }
        .site-footer { padding: 2rem 1.25rem; flex-direction: column; text-align: center; }
    }
    </style>
</head>
<body>

<nav class="site-nav">
    <a href="<?= base_url('/') ?>" class="brand">Studio MarcoSantoFoto</a>
    <div class="nav-links">
        <?php if (session()->has('customer_id')): ?>
            <a href="<?= route_to('client.my_bookings') ?>">Minha Agenda</a>
            <a href="<?= route_to('client.logout') ?>">Sair</a>
        <?php else: ?>
            <a href="<?= route_to('client.access') ?>">Minha Agenda</a>
        <?php endif; ?>
    </div>
</nav>

<?= $this->renderSection('hero') ?>

<main class="site-main">
    <div class="site-container">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="site-alert success">
                <?= esc(session()->getFlashdata('success')) ?>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="site-alert danger">
                <?= esc(session()->getFlashdata('error')) ?>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="site-alert warning">
                <?= esc(session()->getFlashdata('warning')) ?>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="site-alert danger">
                <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                    <div><?= esc($err) ?></div>
                <?php endforeach; ?>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>
</main>

<footer class="site-footer">
    <span class="footer-brand">Studio MarcoSantoFoto</span>
    <span class="footer-copy">© <?= date('Y') ?> — Todos os direitos reservados</span>
</footer>

<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
