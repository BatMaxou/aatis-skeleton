<?php

namespace App\Controller;

use Aatis\DependencyInjection\Interface\ContainerInterface;
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
use Aatis\Tester\EventDispatcher\Subscriber\CustomSubscriber;

class HttpFoundationController extends AbstractController
{
    public function __construct(
        ContainerInterface $container,
        TemplateRendererInterface $templateRenderer,
        private readonly string $_document_root,
    ) {
        parent::__construct($container, $templateRenderer);
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
