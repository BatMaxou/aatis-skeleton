<?php

namespace App\Controller;

use Aatis\DependencyInjection\Enum\ServiceTagOption;
use Aatis\DependencyInjection\Interface\ServiceInstanciatorInterface;
use Aatis\DependencyInjection\Service\ServiceTagBuilder;
use Aatis\EventDispatcher\Interface\EventSubscriberInterface;
use Aatis\EventDispatcher\Service\EventDispatcher;
use Aatis\HttpFoundation\Component\JsonResponse;
use Aatis\HttpFoundation\Component\Response;
use Aatis\ParameterBag;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AbstractController;
use Aatis\Tag\Enum\TagOption;
use Aatis\Tag\Service\TagBuilder;
use Aatis\TemplateRenderer\Interface\TypedTemplateRendererInterface;
use Aatis\Tester\EventDispatcher\Event\CustomStoppableEvent;

class AatisController extends AbstractController
{
    #[Route('/home', ['GET', 'POST'])]
    public function home(): Response
    {
        return $this->render('/pages/home.tpl.php', [
            'title' => 'Home',
        ]);
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

    #[Route('/container')]
    public function container(
        EventDispatcher $eventDispatcher,
        ServiceTagBuilder $serviceTagBuilder,
    ): Response {
        dd($this->container);
        dd($this->container->get($serviceTagBuilder->buildFromInterface(ServiceInstanciatorInterface::class, [ServiceTagOption::SERVICE_TARGETED])));
        $event = new CustomStoppableEvent('This is a message from Stoppable Event !');
        $eventDispatcher->dispatch($event);

        dd($this->container->get($serviceTagBuilder->buildFromInterface(EventSubscriberInterface::class, [ServiceTagOption::SERVICE_TARGETED])));
    }

    #[Route('/serialize')]
    public function serialize(TagBuilder $tagBuilder): Response
    {
        $data = [
            true,
            'title',
            23,
            ['array' => true],
            new \DateTimeImmutable(),
            $tagBuilder
                ->buildFromInterface(TypedTemplateRendererInterface::class, [TagOption::BUILD_OBJECT])
                ->setParameters(new ParameterBag([
                    'test' => 'test',
                ])),
        ];

        foreach ($data as $item) {
            $serialized = serialize($item);
            $unserialized = unserialize($serialized);
            dump($serialized);
            dump($unserialized);
        }

        return new JsonResponse((new ParameterBag(['test' => 'test']))->all());
    }
}
