<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Base controller for all Admin controllers.
 * Ensures the user is authenticated and is an admin.
 */
abstract class BaseAdminController extends BaseController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        // Check authentication (Shield session filter handles this via routes,
        // but we double-check here and verify the group)
        if (! auth()->loggedIn()) {
            redirect()->to('/login')->send();
            exit;
        }

        // Must be admin or superadmin
        $user = auth()->user();
        if (! $user->inGroup('admin', 'superadmin')) {
            redirect()->to('/')->with('error', 'Acesso negado.')->send();
            exit;
        }
    }
}
