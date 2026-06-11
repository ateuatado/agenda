<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

/**
 * Base API controller — returns JSON responses.
 */
abstract class BaseApiController extends BaseController
{
    protected function json(mixed $data, int $statusCode = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($data);
    }

    protected function success(mixed $data = null, string $message = 'OK', int $statusCode = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->json(['success' => true, 'message' => $message, 'data' => $data], $statusCode);
    }

    protected function error(string $message, int $statusCode = 400, mixed $errors = null): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->json(['success' => false, 'message' => $message, 'errors' => $errors], $statusCode);
    }
}
