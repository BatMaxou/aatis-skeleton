<?php

namespace App\Controller;

use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AbstractController;

class TemplateRendererController extends AbstractController
{
    #[Route('/html')]
    public function html(): Response
    {
        $this->render('/html/home.html');
        dd($this->container);
    }

    #[Route('/twig')]
    public function twig(): Response
    {
        return $this->render('/twig/twig.html.twig', [
            'title' => 'Twig',
        ]);
    }

    #[Route('/extra')]
    public function extra(): Response
    {
        return $this->render('/extra/home.extra.php', [
            'title' => 'Extra renderer',
        ]);
    }

    #[Route('/zebi')]
    public function zebi(): Response
    {
        return $this->render('/extra/home.zebi', [
            'title' => 'Zebi renderer',
        ]);
    }
}
