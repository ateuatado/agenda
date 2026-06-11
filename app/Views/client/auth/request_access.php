<?= $this->extend('client/layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
<div class="col-md-5">
    <div class="card">
        <div class="card-body p-4 text-center">
            <div style="font-size:2.5rem">🔑</div>
            <h5 class="fw-700 mt-3 mb-1">Acessar Minha Agenda</h5>
            <p class="text-muted small mb-4">
                Informe seu e-mail e enviaremos um link de acesso seguro. Sem senha necessária!
            </p>
            <form action="<?= route_to('client.access.send') ?>" method="POST" class="text-start">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Seu e-mail</label>
                    <input type="email" name="email" class="form-control" required
                        placeholder="seu@email.com" value="<?= old('email') ?>">
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-send me-1"></i> Enviar link de acesso
                </button>
            </form>
            <hr class="my-3">
            <a href="<?= route_to('client.home') ?>" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Ver horários disponíveis
            </a>
        </div>
    </div>
</div>
</div>

<?= $this->endSection() ?>
