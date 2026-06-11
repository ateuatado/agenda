<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestEmail extends BaseCommand
{
    protected $group       = 'app';
    protected $name        = 'email:send-test';
    protected $description = 'Envia um email de teste via SMTP configurado no .env';
    protected $usage       = 'email:send-test [destinatario]';

    public function run(array $params): void
    {
        $to = $params[0] ?? CLI::prompt('Email de destino');

        CLI::write("Enviando para: $to", 'yellow');

        $email = service('email');
        $email->setFrom(env('email.fromEmail', 'noreply@marcosantofoto.com.br'), env('email.fromName', 'Agenda Test'));
        $email->setTo($to);
        $email->setSubject('Teste SMTP — Agenda MarcoSantoFoto');
        $email->setMessage('<h2>Teste de email</h2><p>Se você recebeu este email, o SMTP está funcionando corretamente.</p>');

        if ($email->send()) {
            CLI::write('✅ Email enviado com sucesso!', 'green');
        } else {
            CLI::write('❌ Falha ao enviar email:', 'red');
            CLI::write($email->printDebugger(['headers', 'subject', 'body']), 'red');
        }
    }
}
