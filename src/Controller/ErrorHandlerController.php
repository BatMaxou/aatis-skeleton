<?php

namespace App\Controller;

use Aatis\DependencyInjection\Exception\FileNotFoundException;
use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AbstractController;

class ErrorHandlerController extends AbstractController
{
    #[Route('/exception')]
    public function loggerError(): Response
    {
        throw new FileNotFoundException('Test exception', 30);
    }

    #[Route('/error')]
    public function loggerException(): Response
    {
        trigger_error('Test error', E_USER_ERROR);
    }
}
