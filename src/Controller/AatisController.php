<?php

namespace App\Controller;

use Aatis\DependencyInjection\Exception\FileNotFoundException;
use Aatis\DependencyInjection\Interface\ContainerInterface;
use Aatis\EventDispatcher\Service\EventDispatcher;
use Aatis\HttpFoundation\Component\File\File;
use Aatis\HttpFoundation\Component\FileResponse;
use Aatis\HttpFoundation\Component\JsonResponse;
use Aatis\HttpFoundation\Component\RedirectResponse;
use Aatis\HttpFoundation\Component\Request;
use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AbstractController;
use Aatis\TemplateRenderer\Interface\TemplateRendererInterface;
use Aatis\Tester\Common\Interface\WriterInterface;
use Aatis\Tester\EventDispatcher\Event\CustomEvent;
use Aatis\Tester\EventDispatcher\Event\CustomStoppableEvent;
use Aatis\Tester\EventDispatcher\Subscriber\CustomSubscriber;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AatisController extends AbstractController
{
    public function __construct(
        ContainerInterface $container,
        TemplateRendererInterface $templateRenderer,
        private readonly WriterInterface $writer,
        private readonly EventDispatcher $eventDispatcher,
        private readonly string $_document_root,
        private readonly ?LoggerInterface $logger = null,
    ) {
        parent::__construct($container, $templateRenderer);
    }

    #[Route('/home', ['GET', 'POST'])]
    public function home(): Response
    {
        return $this->render('/pages/home.tpl.php', [
            'title' => 'Home',
        ]);
    }

    #[Route('/container')]
    public function container(): Response
    {
        dd($this->container);
    }

    #[Route('/hello')]
    public function hello(): Response
    {
        return $this->render('/pages/hello.tpl.php', [
            'title' => 'Hello Aatis ?',
        ]);
    }

    #[Route('/hello/{name}')]
    public function helloName(string $name): Response
    {
        return $this->render('/pages/helloName.tpl.php', [
            'title' => 'Hello '.$name.' !',
            'name' => $name,
        ]);
    }

    #[Route('/html')]
    public function html(): Response
    {
        return $this->render('/html/home.html');
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

    #[Route('/permission')]
    public function permission(): Response
    {
        exec('ls -al ../', $outputLs);
        exec('whoami', $outputUser);

        dd($outputLs, $outputUser);
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

    #[Route('/http/response', ['POST'])]
    public function httpResponse(): Response
    {
        return $this->render('/pages/helloName.tpl.php', [
            'title' => 'Hello HTTP foundation !',
            'name' => 'HTTP Foundation',
        ]);
    }

    #[Route('/route/injection/{name}')]
    public function routeInjection(
        Request $request,
        CustomSubscriber $customService,
        WriterInterface $interface,
        string $name,
        string $_document_root,
    ): Response {
        dd([
            'request' => $request,
            'customService' => $customService,
            'interface' => $interface,
            'name' => $name,
            '_document_root' => $_document_root,
        ]);
    }

    #[Route('/redirect')]
    public function redirect(): Response
    {
        return new RedirectResponse('/home');
    }

    #[Route('/file')]
    public function file(): Response
    {
        $file = new File(sprintf('%s/pdf/file.pdf', $this->_document_root));
        $file = new File(sprintf('%s/../config/services.yaml', $this->_document_root));

        return new FileResponse($file);
    }

    #[Route('/file/methods')]
    public function fileMethods(): Response
    {
        $file = new File(sprintf('%s/../test.txt', $this->_document_root));

        dd($file);

        $file->append("This is a test file !\n");

        return new Response($file->getContents());
    }

    #[Route('/json')]
    public function json(): Response
    {
        return new JsonResponse(json_encode([
            'title' => 'Hello JSON !',
            'name' => 'JSON',
        ]));
    }

    #[Route('/file/bag')]
    public function fileBag(Request $request): Response
    {
        $file = $request->files->get('testFile');

        if (null !== $file) {
            $file->save(sprintf('%s/../uploads', $this->_document_root));
        }

        return new Response('', 204);
    }

    #[Route('/file/duplicate')]
    public function fileDuplicate(): Response
    {
        $file = new File(sprintf('%s/../uploads/infos.md', $this->_document_root));
        $file->setOverrideName('zebi');

        $file->save(sprintf('%s/../uploads', $this->_document_root));

        return new Response('', 204);
    }
}
