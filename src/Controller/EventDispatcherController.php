<?php

namespace App\Controller;

use Aatis\DependencyInjection\Interface\ContainerInterface;
use Aatis\EventDispatcher\Service\EventDispatcher;
use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AbstractController;
use Aatis\TemplateRenderer\Interface\TemplateRendererInterface;
use Aatis\Tester\EventDispatcher\Event\CustomEvent;
use Aatis\Tester\EventDispatcher\Event\CustomStoppableEvent;

class EventDispatcherController extends AbstractController
{
    public function __construct(
        ContainerInterface $container,
        TemplateRendererInterface $templateRenderer,
        private readonly EventDispatcher $eventDispatcher,
    ) {
        parent::__construct($container, $templateRenderer);
    }

    #[Route('/event')]
    public function event(): Response
    {
        $event = new CustomEvent('This is a message from Custom Event !');
        $this->eventDispatcher->dispatch($event);

        return $this->render('/pages/helloName.tpl.php', [
            'title' => 'Hello custom event !',
            'name' => 'Event',
        ]);
    }

    #[Route('/stoppable-event')]
    public function stoppableEvent(): Response
    {
        $event = new CustomStoppableEvent('This is a message from Stoppable Event !');
        $this->eventDispatcher->dispatch($event);

        return $this->render('/pages/helloName.tpl.php', [
            'title' => 'Hello stoppable event !',
            'name' => 'Stoppable Event',
        ]);
    }
}
