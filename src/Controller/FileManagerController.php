<?php

namespace App\Controller;

use Aatis\FileManager\Exception\FileNotFoundException;
use Aatis\FileManager\Interface\FileManagerInterface;
use Aatis\HttpFoundation\Component\JsonResponse;
use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AbstractController;

class FileManagerController extends AbstractController
{
    #[Route('/permission')]
    public function permission(): Response
    {
        exec('ls -al ../', $outputLs);
        exec('whoami', $outputUser);

        dd($outputLs, $outputUser);
    }

    #[Route('/file-manager/file/exists')]
    public function fileExists(FileManagerInterface $fm, string $_document_root): Response
    {
        $exists = $fm->exists(sprintf('%s/../var/cache/biroute/of/fame/biroute_1.txt', $_document_root));

        return new Response((string) $exists, 200);
    }

    #[Route('/file-manager/folder/exists')]
    public function existFile(FileManagerInterface $fm, string $_document_root): Response
    {
        $exists = $fm->exists(sprintf('%s/../var/cache/biroute/of/fame/', $_document_root));

        return new Response((string) $exists, 200);
    }

    #[Route('/file-manager/file/create')]
    public function createFile(FileManagerInterface $fm, string $_document_root): Response
    {
        $fm->createFile(sprintf('%s/../var/cache/biroute/of/fame/biroute_1.txt', $_document_root), recursive: true);

        return new Response('OK', 200);
    }

    #[Route('/file-manager/folder/create')]
    public function createDirectory(FileManagerInterface $fm, string $_document_root): Response
    {
        $fm->createDirectory(sprintf('%s/../var/cache/biroute/of/fame', $_document_root), recursive: true);

        return new Response('OK', 200);
    }

    #[Route('/file-manager/file/delete')]
    public function deleteFile(FileManagerInterface $fm, string $_document_root): Response
    {
        $fm->deleteFile(sprintf('%s/../var/cache/biroute/of/fame/biroute_1.txt', $_document_root));

        return new Response('OK', 200);
    }

    #[Route('/file-manager/folder/delete')]
    public function deleteDirectory(FileManagerInterface $fm, string $_document_root): Response
    {
        $fm->deleteDirectory(sprintf('%s/../var/cache/biroute', $_document_root), true);

        return new Response('OK', 200);
    }

    #[Route('/file-manager/file/read')]
    public function readFile(FileManagerInterface $fm, string $_document_root): Response
    {
        $file = sprintf('%s/../var/cache/biroute/of/fame/biroute_1.txt', $_document_root);

        try {
            $content = $fm->read(sprintf('%s/../var/cache/biroute/of/fame/biroute_1.txt', $_document_root));
        } catch (FileNotFoundException) {
            return new Response('File does not exist', 404);
        }

        return new JsonResponse($content, 200);
    }

    #[Route('/file-manager/file/write')]
    public function writeFile(FileManagerInterface $fm, string $_document_root): Response
    {
        $file = sprintf('%s/../var/cache/biroute/of/fame/biroute_1.txt', $_document_root);

        try {
            $fm->write($file, 'This is a test file !');
        } catch (FileNotFoundException) {
            return new Response('File does not exist', 404);
        }

        return new Response('File written successfully', 200);
    }

    #[Route('/file-manager/file/append')]
    public function appendFile(FileManagerInterface $fm, string $_document_root): Response
    {
        $file = sprintf('%s/../var/cache/biroute/of/fame/biroute_1.txt', $_document_root);

        try {
            $fm->append($file, "This is a test file !\n");
        } catch (FileNotFoundException) {
            return new Response('File does not exist', 404);
        }

        return new Response('File appended successfully', 200);
    }
}
