<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>

<div class="api-docs-wrapper">

    <!-- Sidebar de navegação interna -->
    <nav class="api-docs-nav" id="apiNav">
        <p class="api-nav-label">Geral</p>
        <a href="#formato">Formato de Resposta</a>
        <a href="#cors">CORS</a>

        <p class="api-nav-label">Autenticação</p>
        <a href="#auth-request">POST /auth/request-access</a>
        <a href="#auth-verify">POST /auth/verify</a>

        <p class="api-nav-label">Endpoints Públicos</p>
        <a href="#availability">GET /availability</a>
        <a href="#availability-date">GET /availability/{date}</a>
        <a href="#book">POST /book</a>
        <a href="#interest">POST /interest</a>

        <p class="api-nav-label">Cliente Autenticado</p>
        <a href="#my-bookings">GET /my-bookings</a>
        <a href="#cancel">DELETE /bookings/{id}</a>
        <a href="#interests">GET /interests</a>
    </nav>

    <!-- Conteúdo principal -->
    <div class="api-docs-content">

        <!-- Header -->
        <div class="api-docs-header mb-4">
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="api-badge-version">v1</span>
                <h1 class="api-title mb-0">API REST — MarcoSantoFoto</h1>
            </div>
            <p class="text-muted mb-3">
                Interface de programação para integração com landing pages, widgets de agendamento e apps externos.
            </p>
            <div class="api-base-urls">
                <div class="api-base-url">
                    <span class="label">Produção</span>
                    <code><?= esc(rtrim($baseUrl, '/')) ?>/api/v1</code>
                </div>
                <div class="api-base-url">
                    <span class="label">Dev</span>
                    <code>https://agenda.test/api/v1</code>
                </div>
            </div>
        </div>

        <!-- ── Formato de Resposta ─────────────────────────────────── -->
        <section id="formato" class="api-section">
            <h2>Formato de Resposta</h2>
            <p>Todas as respostas seguem o mesmo envelope JSON:</p>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": true | false,
  "message": "Descrição do resultado",
  "data": { } | [ ] | null
}</pre>
            </div>
            <p class="mt-3">Erros de validação incluem um campo <code>errors</code>:</p>
            <div class="code-block">
                <div class="code-lang">JSON — 422</div>
                <pre>{
  "success": false,
  "message": "Dados inválidos.",
  "errors": {
    "email": "The email field must contain a valid email address."
  }
}</pre>
            </div>
        </section>

        <!-- ── CORS ───────────────────────────────────────────────── -->
        <section id="cors" class="api-section">
            <h2>CORS</h2>
            <p>A API aceita requisições cross-origin de:</p>
            <ul>
                <li><code>*.marcosantofoto.com.br</code></li>
                <li><code>agenda.test</code> (desenvolvimento)</li>
            </ul>
            <p class="text-muted small">Outras origens recebem <code>Access-Control-Allow-Origin: *</code>. Métodos permitidos: GET, POST, PUT, DELETE, OPTIONS.</p>
        </section>

        <!-- ── Auth: request-access ───────────────────────────────── -->
        <section id="auth-request" class="api-section">
            <div class="endpoint-header">
                <span class="http-method post">POST</span>
                <span class="endpoint-path">/api/v1/auth/request-access</span>
                <span class="endpoint-badge public">Público</span>
            </div>
            <p>Envia um magic link para o e-mail informado. O cliente receberá um link com um token de acesso temporário.</p>

            <h6>Body <span class="text-muted fw-normal">application/json</span></h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "email": "cliente@exemplo.com"
}</pre>
            </div>

            <h6 class="mt-3">Resposta <span class="badge bg-success ms-1">200</span></h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": true,
  "message": "Se este e-mail estiver cadastrado, você receberá o link em instantes.",
  "data": null
}</pre>
            </div>
            <div class="api-note">
                <i class="bi bi-shield-check me-1"></i>
                A resposta é idêntica independente de o e-mail existir ou não (segurança contra enumeração).
            </div>

            <h6 class="mt-3">Exemplo cURL</h6>
            <div class="code-block">
                <div class="code-lang">bash</div>
                <pre>curl -X POST "<?= esc(rtrim($baseUrl, '/')) ?>/api/v1/auth/request-access" \
  -H "Content-Type: application/json" \
  -d '{"email": "cliente@exemplo.com"}'</pre>
            </div>
        </section>

        <!-- ── Auth: verify ───────────────────────────────────────── -->
        <section id="auth-verify" class="api-section">
            <div class="endpoint-header">
                <span class="http-method post">POST</span>
                <span class="endpoint-path">/api/v1/auth/verify</span>
                <span class="endpoint-badge public">Público</span>
            </div>
            <p>Troca o token do magic link por um <strong>access_token</strong> de longa duração (7 dias). Use-o como <code>Bearer</code> nos endpoints autenticados.</p>

            <h6>Body</h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "token": "abc123tokenDoMagicLink"
}</pre>
            </div>

            <h6 class="mt-3">Resposta <span class="badge bg-success ms-1">200</span></h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": true,
  "message": "Autenticado com sucesso.",
  "data": {
    "access_token": "eyJ...",
    "customer": {
      "id": 42,
      "name": "Margarida Santos",
      "email": "cliente@exemplo.com"
    }
  }
}</pre>
            </div>

            <h6 class="mt-3">Erro <span class="badge bg-danger ms-1">401</span></h6>
            <div class="code-block err">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": false,
  "message": "Token inválido ou expirado.",
  "errors": null
}</pre>
            </div>
        </section>

        <!-- ── GET /availability ──────────────────────────────────── -->
        <section id="availability" class="api-section">
            <div class="endpoint-header">
                <span class="http-method get">GET</span>
                <span class="endpoint-path">/api/v1/availability</span>
                <span class="endpoint-badge public">Público</span>
            </div>
            <p>Lista todos os slots com <code>status = available</code> do mês informado.</p>

            <h6>Query params</h6>
            <table class="params-table">
                <tr><th>Param</th><th>Tipo</th><th>Padrão</th><th>Descrição</th></tr>
                <tr><td><code>year</code></td><td>int</td><td>ano atual</td><td>Ex: <code>2026</code></td></tr>
                <tr><td><code>month</code></td><td>int</td><td>mês atual</td><td>Ex: <code>4</code></td></tr>
            </table>

            <h6 class="mt-3">Resposta <span class="badge bg-success ms-1">200</span></h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 2,
      "session_type_id": 1,
      "date": "2026-04-07",
      "start_time": "08:00:00",
      "end_time": "10:00:00",
      "status": "available",
      "notes": null,
      "session_type_name": "Ensaio 2 horas",
      "duration_minutes": 120,
      "color": "#6366f1"
    }
  ]
}</pre>
            </div>

            <h6 class="mt-3">Exemplo cURL</h6>
            <div class="code-block">
                <div class="code-lang">bash</div>
                <pre>curl "<?= esc(rtrim($baseUrl, '/')) ?>/api/v1/availability?year=2026&month=4"</pre>
            </div>
        </section>

        <!-- ── GET /availability/{date} ──────────────────────────── -->
        <section id="availability-date" class="api-section">
            <div class="endpoint-header">
                <span class="http-method get">GET</span>
                <span class="endpoint-path">/api/v1/availability/{date}</span>
                <span class="endpoint-badge public">Público</span>
            </div>
            <p>Retorna os slots disponíveis de uma data específica. O parâmetro <code>date</code> deve estar no formato <code>YYYY-MM-DD</code>.</p>

            <h6>Exemplo cURL</h6>
            <div class="code-block">
                <div class="code-lang">bash</div>
                <pre>curl "<?= esc(rtrim($baseUrl, '/')) ?>/api/v1/availability/2026-04-14"</pre>
            </div>
        </section>

        <!-- ── POST /book ─────────────────────────────────────────── -->
        <section id="book" class="api-section">
            <div class="endpoint-header">
                <span class="http-method post">POST</span>
                <span class="endpoint-path">/api/v1/book</span>
                <span class="endpoint-badge public">Público</span>
            </div>
            <p>Cria um agendamento. O slot deve ter <code>status = available</code>. O cliente é criado automaticamente se ainda não existir.</p>

            <h6>Body</h6>
            <table class="params-table">
                <tr><th>Campo</th><th>Tipo</th><th>Obrigatório</th><th>Descrição</th></tr>
                <tr><td><code>slot_id</code></td><td>int</td><td><span class="req">✓</span></td><td>ID do slot retornado por <code>/availability</code></td></tr>
                <tr><td><code>name</code></td><td>string</td><td><span class="req">✓</span></td><td>Nome completo (mín. 2 chars)</td></tr>
                <tr><td><code>email</code></td><td>string</td><td><span class="req">✓</span></td><td>E-mail válido</td></tr>
                <tr><td><code>phone</code></td><td>string</td><td><span class="req">✓</span></td><td>Telefone (mín. 8 chars)</td></tr>
                <tr><td><code>notes</code></td><td>string</td><td>—</td><td>Observações do cliente</td></tr>
            </table>

            <h6 class="mt-3">Resposta <span class="badge bg-success ms-1">201</span></h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": true,
  "message": "Agendamento confirmado!",
  "data": { "booking_id": 7 }
}</pre>
            </div>

            <h6 class="mt-3">Erro <span class="badge bg-danger ms-1">409</span> — Slot indisponível</h6>
            <div class="code-block err">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": false,
  "message": "Este horário não está disponível.",
  "errors": null
}</pre>
            </div>

            <h6 class="mt-3">Exemplo cURL</h6>
            <div class="code-block">
                <div class="code-lang">bash</div>
                <pre>curl -X POST "<?= esc(rtrim($baseUrl, '/')) ?>/api/v1/book" \
  -H "Content-Type: application/json" \
  -d '{
    "slot_id": 2,
    "name": "Margarida Santos",
    "email": "margarida@email.com",
    "phone": "11999998888",
    "notes": "Prefiro fotos ao ar livre."
  }'</pre>
            </div>
        </section>

        <!-- ── POST /interest ────────────────────────────────────── -->
        <section id="interest" class="api-section">
            <div class="endpoint-header">
                <span class="http-method post">POST</span>
                <span class="endpoint-path">/api/v1/interest</span>
                <span class="endpoint-badge public">Público</span>
            </div>
            <p>Registra interesse em um slot ocupado. O cliente entra na lista de espera e é notificado por e-mail se o slot abrir.</p>

            <h6>Body</h6>
            <table class="params-table">
                <tr><th>Campo</th><th>Obrigatório</th></tr>
                <tr><td><code>slot_id</code></td><td><span class="req">✓</span></td></tr>
                <tr><td><code>name</code></td><td><span class="req">✓</span></td></tr>
                <tr><td><code>email</code></td><td><span class="req">✓</span></td></tr>
                <tr><td><code>phone</code></td><td>—</td></tr>
            </table>

            <h6 class="mt-3">Resposta <span class="badge bg-success ms-1">201</span></h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": true,
  "message": "Interesse registrado!",
  "data": null
}</pre>
            </div>
        </section>

        <!-- ── GET /my-bookings ───────────────────────────────────── -->
        <section id="my-bookings" class="api-section">
            <div class="endpoint-header">
                <span class="http-method get">GET</span>
                <span class="endpoint-path">/api/v1/my-bookings</span>
                <span class="endpoint-badge auth">🔒 Autenticado</span>
            </div>
            <p>Lista os agendamentos ativos do cliente autenticado.</p>

            <div class="api-note auth-note">
                <i class="bi bi-key me-1"></i>
                Requer header: <code>Authorization: Bearer {access_token}</code>
            </div>

            <h6 class="mt-3">Exemplo cURL</h6>
            <div class="code-block">
                <div class="code-lang">bash</div>
                <pre>curl -H "Authorization: Bearer SEU_TOKEN" \
  "<?= esc(rtrim($baseUrl, '/')) ?>/api/v1/my-bookings"</pre>
            </div>

            <h6 class="mt-3">Resposta <span class="badge bg-success ms-1">200</span></h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 7,
      "status": "confirmed",
      "booked_at": "2026-04-07 10:30:00",
      "date": "2026-04-14",
      "start_time": "08:00:00",
      "end_time": "10:00:00",
      "session_type_name": "Ensaio 2 horas",
      "duration_minutes": 120,
      "color": "#6366f1"
    }
  ]
}</pre>
            </div>
        </section>

        <!-- ── DELETE /bookings/{id} ──────────────────────────────── -->
        <section id="cancel" class="api-section">
            <div class="endpoint-header">
                <span class="http-method delete">DELETE</span>
                <span class="endpoint-path">/api/v1/bookings/{id}</span>
                <span class="endpoint-badge auth">🔒 Autenticado</span>
            </div>
            <p>Cancela um agendamento do cliente. O slot volta automaticamente para <code>available</code> e o próximo da lista de espera é notificado.</p>

            <div class="api-note auth-note">
                <i class="bi bi-key me-1"></i>
                Requer header: <code>Authorization: Bearer {access_token}</code>
            </div>

            <h6 class="mt-3">Exemplo cURL</h6>
            <div class="code-block">
                <div class="code-lang">bash</div>
                <pre>curl -X DELETE \
  -H "Authorization: Bearer SEU_TOKEN" \
  "<?= esc(rtrim($baseUrl, '/')) ?>/api/v1/bookings/7"</pre>
            </div>

            <h6 class="mt-3">Resposta <span class="badge bg-success ms-1">200</span></h6>
            <div class="code-block">
                <div class="code-lang">JSON</div>
                <pre>{ "success": true, "message": "Agendamento cancelado.", "data": null }</pre>
            </div>
        </section>

        <!-- ── GET /interests ─────────────────────────────────────── -->
        <section id="interests" class="api-section">
            <div class="endpoint-header">
                <span class="http-method get">GET</span>
                <span class="endpoint-path">/api/v1/interests</span>
                <span class="endpoint-badge auth">🔒 Autenticado</span>
            </div>
            <p>Lista os interesses (lista de espera) do cliente autenticado.</p>

            <div class="api-note auth-note">
                <i class="bi bi-key me-1"></i>
                Requer header: <code>Authorization: Bearer {access_token}</code>
            </div>

            <h6 class="mt-3">Exemplo cURL</h6>
            <div class="code-block">
                <div class="code-lang">bash</div>
                <pre>curl -H "Authorization: Bearer SEU_TOKEN" \
  "<?= esc(rtrim($baseUrl, '/')) ?>/api/v1/interests"</pre>
            </div>
        </section>

    </div><!-- /.api-docs-content -->
</div><!-- /.api-docs-wrapper -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
/* ── Layout ───────────────────────────────────────────────────────── */
.api-docs-wrapper {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 2rem;
    align-items: start;
}

/* ── Nav Lateral ──────────────────────────────────────────────────── */
.api-docs-nav {
    position: sticky;
    top: 1.5rem;
    background: var(--card-bg, #fff);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem 1rem;
    font-size: .82rem;
}
.api-docs-nav a {
    display: block;
    padding: .3rem .75rem;
    color: #64748b;
    text-decoration: none;
    border-radius: 6px;
    margin-bottom: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: background .15s, color .15s;
}
.api-docs-nav a:hover { background: #f1f5f9; color: #1e293b; }
.api-nav-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #94a3b8;
    margin: .9rem 0 .3rem .75rem;
}

/* ── Seções ───────────────────────────────────────────────────────── */
.api-section {
    padding-bottom: 2.5rem;
    margin-bottom: 2.5rem;
    border-bottom: 1px solid #e2e8f0;
}
.api-section:last-child { border-bottom: none; margin-bottom: 0; }
.api-section h2 {
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: .75rem;
    color: #1e293b;
}
.api-section h6 { font-size: .85rem; font-weight: 600; margin-top: 1rem; margin-bottom: .4rem; }

/* ── Header da página ─────────────────────────────────────────────── */
.api-docs-header { border-bottom: 2px solid #e2e8f0; padding-bottom: 1.5rem; margin-bottom: 2rem; }
.api-title { font-size: 1.4rem; font-weight: 800; color: #1e293b; }
.api-badge-version {
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    color: #fff;
    font-size: .7rem;
    font-weight: 700;
    padding: .2rem .6rem;
    border-radius: 20px;
    letter-spacing: .05em;
}
.api-base-urls { display: flex; gap: 1rem; flex-wrap: wrap; }
.api-base-url {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .4rem .9rem;
    display: flex;
    align-items: center;
    gap: .6rem;
    font-size: .82rem;
}
.api-base-url .label { color: #94a3b8; font-size: .72rem; font-weight: 600; text-transform: uppercase; }

/* ── Endpoint header ──────────────────────────────────────────────── */
.endpoint-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: .75rem;
    flex-wrap: wrap;
}
.http-method {
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .08em;
    padding: .25rem .6rem;
    border-radius: 6px;
    min-width: 56px;
    text-align: center;
}
.http-method.get    { background: #dbeafe; color: #1d4ed8; }
.http-method.post   { background: #dcfce7; color: #166534; }
.http-method.delete { background: #fee2e2; color: #991b1b; }
.http-method.put    { background: #fef3c7; color: #92400e; }

.endpoint-path {
    font-family: 'Courier New', monospace;
    font-size: .9rem;
    font-weight: 600;
    color: #1e293b;
}
.endpoint-badge {
    font-size: .7rem;
    font-weight: 600;
    padding: .2rem .6rem;
    border-radius: 20px;
    margin-left: auto;
}
.endpoint-badge.public { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.endpoint-badge.auth   { background: #fefce8; color: #854d0e; border: 1px solid #fde68a; }

/* ── Code blocks ──────────────────────────────────────────────────── */
.code-block {
    position: relative;
    background: #0f172a;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: .5rem;
}
.code-block.err { background: #1c0a0a; }
.code-block pre {
    margin: 0;
    padding: 1rem 1.25rem;
    font-family: 'Courier New', monospace;
    font-size: .8rem;
    line-height: 1.65;
    color: #e2e8f0;
    white-space: pre;
    overflow-x: auto;
}
.code-lang {
    background: #1e293b;
    color: #64748b;
    font-size: .65rem;
    font-weight: 700;
    padding: .2rem .75rem;
    letter-spacing: .08em;
    text-transform: uppercase;
}

/* ── Tabela de parâmetros ─────────────────────────────────────────── */
.params-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .83rem;
    margin-bottom: .25rem;
}
.params-table th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: .4rem .75rem;
    border: 1px solid #e2e8f0;
}
.params-table td {
    padding: .45rem .75rem;
    border: 1px solid #e2e8f0;
    color: #334155;
    vertical-align: middle;
}
.req { color: #ef4444; font-weight: 700; }

/* ── Notes ────────────────────────────────────────────────────────── */
.api-note {
    background: #f0f9ff;
    border-left: 3px solid #38bdf8;
    border-radius: 0 8px 8px 0;
    padding: .6rem 1rem;
    font-size: .82rem;
    color: #0369a1;
    margin-top: .75rem;
}
.api-note.auth-note {
    background: #fefce8;
    border-left-color: #fbbf24;
    color: #92400e;
}

/* ── Responsivo ───────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .api-docs-wrapper { grid-template-columns: 1fr; }
    .api-docs-nav { position: static; }
}
</style>

<script>
// Highlight active nav link on scroll
(function() {
    const sections = document.querySelectorAll('.api-section[id]');
    const links    = document.querySelectorAll('.api-docs-nav a');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                links.forEach(l => l.style.background = '');
                const active = document.querySelector(`.api-docs-nav a[href="#${entry.target.id}"]`);
                if (active) { active.style.background = '#e0e7ff'; active.style.color = '#4338ca'; }
            }
        });
    }, { rootMargin: '-30% 0px -60% 0px' });

    sections.forEach(s => observer.observe(s));
})();
</script>
<?= $this->endSection() ?>
