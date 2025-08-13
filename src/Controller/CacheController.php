<?php

namespace App\Controller;

use Aatis\Cache\Component\Recipe;
use Aatis\Cache\Interface\CacheSystemInterface;
use Aatis\Cache\Service\CacheRecipePool;
use Aatis\DependencyInjection\Component\Service;
use Aatis\DependencyInjection\Interface\ContainerInterface;
use Aatis\DependencyInjection\Service\ServiceFactory;
use Aatis\DependencyInjection\Service\ServiceTagBuilder;
use Aatis\HttpFoundation\Component\JsonResponse;
use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AbstractController;
use Aatis\Tag\Service\TagBuilder;
use Aatis\TemplateRenderer\Interface\TemplateRendererInterface;
use Aatis\Tester\Cache\Service\CacheTestPool;
use Aatis\Tester\Common\Service\Writer;

class CacheController extends AbstractController
{
    public function __construct(
        ContainerInterface $container,
        TemplateRendererInterface $templateRenderer,
        private readonly CacheSystemInterface $cacheSystem,
    ) {
        parent::__construct($container, $templateRenderer);
    }

    #[Route('/cache/set')]
    public function set(): Response
    {
        $this->cacheSystem->set('test', 'This is a test value', CacheSystemInterface::SECOND * 2);
        $this->cacheSystem->set('test_2', true);
        $this->cacheSystem->set('test_3', null);
        $this->cacheSystem->set('test_4', 1234567890);

        return new Response('', 200);
    }

    #[Route('/cache/defer')]
    public function defer(): Response
    {
        $this->cacheSystem->defer('test_defer', 'This is a deferred test value');
        $this->cacheSystem->defer('test_defer_2', true);
        $this->cacheSystem->defer('test_defer_3', null);
        $this->cacheSystem->defer('test_defer_4', 1234567890);

        sleep(3);

        $this->cacheSystem->commit();

        return new Response('', 200);
    }

    #[Route('/cache/get')]
    public function get(): Response
    {
        $results = [
            'test' => $this->cacheSystem->getItem('test')->get(),
            'test_2' => $this->cacheSystem->getItem('test_2')->get(),
            'test_3' => $this->cacheSystem->getItem('test_3')->get(),
            'test_4' => $this->cacheSystem->getItem('test_4')->get(),
        ];

        dump($results);
        dd(iterator_to_array($this->cacheSystem->getItems(['test_defer', 'test_defer_2', 'test_defer_3', 'test_defer_4'])));

        return new JsonResponse($results, 200);
    }

    #[Route('/cache/has')]
    public function has(): Response
    {
        dump($this->cacheSystem->hasItem('test_4'));
        dd($this->cacheSystem->getItem('test_4'));

        return new Response('', 200);
    }

    #[Route('/cache/delete')]
    public function delete(): Response
    {
        $this->cacheSystem->deleteItem('test');
        $this->cacheSystem->deleteItem('test_2');

        $this->cacheSystem->deleteItems(['test_3', 'test_4', 'test_defer', 'test_defer_2', 'test_defer_3', 'test_defer_4']);

        return new Response('', 200);
    }

    #[Route('/cache/clear')]
    public function clear(): Response
    {
        dd($this->cacheSystem->clear(CacheRecipePool::NAME));

        return new Response('', 200);
    }

    #[Route('/cache/sweep')]
    public function sweep(): Response
    {
        dd($this->cacheSystem->sweep(CacheSystemInterface::ALL_POOLS));

        return new Response('', 200);
    }

    #[Route('/cache/extend/set')]
    public function extendSet(): Response
    {
        $this->cacheSystem->set('test', 'This is a test value', CacheSystemInterface::SECOND * 2, CacheTestPool::NAME);
        $this->cacheSystem->set('test_2', true, pool: CacheTestPool::NAME);
        $this->cacheSystem->set('test_3', null, pool: CacheTestPool::NAME);
        $this->cacheSystem->set('test_4', 1234567890, pool: CacheTestPool::NAME);

        return new Response('', 200);
    }

    #[Route('/cache/recipe/set')]
    public function recipeSet(): Response
    {
        /** @var Recipe<ServiceTagBuilder> $tagBuilderRecipe */
        $tagBuilderRecipe = new Recipe(ServiceTagBuilder::class);
        $tagBuilder = $tagBuilderRecipe->make();

        /** @var Recipe<ServiceFactory, array{tagBuilder: ServiceTagBuilder}> */
        $serviceFactoryRecipe = (new Recipe(
            ServiceFactory::class,
            ['tagBuilder' => $tagBuilder],
            fn ($_, $ingredients) => new ServiceFactory($ingredients['tagBuilder'], []),
        ));

        $writerRecipe = new Recipe(
            Writer::class,
            [
                'defaultMessage' => 'This is the default message',
                'array' => [
                    'classic',
                    null,
                    'string' => 'value',
                    'number' => 2,
                    'nested' => [
                        'date' => new \DateTimeImmutable(),
                        'nested-2' => [1, 2, 3],
                    ],
                ],
            ],
            fn ($class, $ingredients) => new $class($ingredients['defaultMessage'], $ingredients['array']),
        );

        $writerRecipe->addStep(fn ($writer, $ingredients) => $writer->write('Step 1: Writer initialized.'));
        $writerRecipe->addStep(fn ($writer, $ingredients) => $writer->write(' Step 2: Default message is "'.$ingredients['defaultMessage'].'".'));
        $writerRecipe->addStep(fn ($writer, $ingredients) => $writer->write());

        dump($this->cacheSystem->set(Writer::class, $writerRecipe, pool: CacheRecipePool::NAME));

        $serviceBase = $serviceFactoryRecipe->make()->create(Writer::class);

        $serviceRecipe = (new Recipe(
            Service::class,
            [
                'tags' => $serviceBase->getTags(),
                'abstracts' => $serviceBase->getAbstracts(),
                // 'instance' => require(__DIR__.'/../Aatis_Tester_Common_Service_WriterRecipe.php')
                'instance' => $writerRecipe->make(),
            ],
            fn ($class, $ingredients) => (new $class($ingredients['instance']::class))
                ->setAbstracts($ingredients['abstracts'])
                ->setTags($ingredients['tags']),
        ))->addStep(fn ($service, $ingredients) => $service->setInstance($ingredients['instance']));

        // dd($this->cacheSystem->set(Writer::class.'-Service', $serviceRecipe, CacheSystemInterface::SECOND *2, pool: CacheRecipePool::NAME));

        $item = $this->cacheSystem->getItem(Writer::class, pool: CacheRecipePool::NAME);
        if (!$item->isHit()) {
            dd('Not found');
        }

        dd($item->get());

        return new Response('');
    }

    #[Route('/cache/recipe/get')]
    public function recipeGet(): Response
    {
        dd($this->cacheSystem->getItem(Writer::class, pool: CacheRecipePool::NAME)->get()->make());
    }

    #[Route('/cache/recipe/set/circular')]
    public function recipeSetCircular(): Response
    {
        $testRecipe1 = new Recipe(Test::class, ['id' => 1], fn ($class, $ingredients) => new $class($ingredients['id']));
        $testRecipe2 = new Recipe(Test::class, ['id' => 2, 'test' => $testRecipe1], fn ($class, $ingredients) => new $class($ingredients['id']));
        $testRecipe2->addStep(fn ($test, $ingredients) => $test->setTest($ingredients['test']->make()));

        $testRecipe1->addIngredient('test', $testRecipe2);
        $testRecipe1->addStep(fn ($test, $ingredients) => $test->setTest($ingredients['test']->make()));

        $this->cacheSystem->set(Test::class, $testRecipe1, pool: CacheRecipePool::NAME);

        $item = $this->cacheSystem->getItem(Test::class, pool: CacheRecipePool::NAME);
        if (!$item->isHit()) {
            dd('Not found');
        }

        dd($item->get()->make());
    }
}

class Test {
    private ?Test $test = null;

    public function __construct(private int $id)
    {
    }

    public function setTest(Test $test): void
    {
        $this->test = $test;
    }
}
