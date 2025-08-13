<?php

namespace App\Controller;

use Aatis\DependencyInjection\Interface\ContainerInterface;
use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AbstractController;
use Aatis\TemplateRenderer\Interface\TemplateRendererInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerController extends AbstractController
{
    public function __construct(
        ContainerInterface $container,
        TemplateRendererInterface $templateRenderer,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($container, $templateRenderer);
    }

    #[Route('/logger')]
    public function logger(): Response
    {
        $this->logger->info('Logger is working !');

        return $this->render('/pages/helloName.tpl.php', [
            'title' => 'Hello logger !',
            'name' => 'Logger',
        ]);
    }

    #[Route('/logger/{specific}')]
    public function loggerSpecific(string $specific): Response
    {
        $this->logger->log(LogLevel::DEBUG, 'Logger is {test.context} !', ['test.context' => $specific]);
        $this->logger->info('Logger is {test.context} !', ['test.context' => $specific]);
        $this->logger->notice('Logger is {test.context} !', ['test.context' => $specific]);
        $this->logger->warning('Logger is {test.context} !', ['test.context' => $specific]);
        $this->logger->error('Logger is {test.context} !', ['test.context' => $specific]);
        $this->logger->critical('Logger is {test.context} !', ['test.context' => $specific]);
        $this->logger->alert('Logger is {test.context} !', ['test.context' => $specific]);
        $this->logger->emergency('Logger is {test.context} !', ['test.context' => $specific]);

        return $this->render('/pages/helloName.tpl.php', [
            'title' => 'Hello logger '.$specific.' !',
            'name' => 'Logger',
        ]);
    }
}
