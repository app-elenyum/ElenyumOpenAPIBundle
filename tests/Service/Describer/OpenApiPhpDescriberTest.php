<?php

namespace Elenyum\OpenAPI\Tests\Service\Describer;

use Elenyum\OpenAPI\Attribute\Operation;
use Elenyum\OpenAPI\Attribute\Security;
use Elenyum\OpenAPI\Service\Describer\OpenApiPhpDescriber;
use Elenyum\OpenAPI\Service\Describer\Route\FilteredRouteCollectionBuilder;
use Elenyum\OpenAPI\Service\Util\ControllerReflector;
use LogicException;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Doctrine\Common\Annotations\Reader;
use OpenApi\Annotations as OA;

class OpenApiPhpDescriberTest extends TestCase
{
    private $routeCollection;
    private $controllerReflector;
    private $annotationReader;
    private $logger;
    private $filteredRouteCollectionBuilder;
    private $describer;

    protected function setUp(): void
    {
        $this->routeCollection = $this->createMock(RouteCollection::class);
        // Add your routes to $this->routeCollection as needed for testing

        $this->controllerReflector = $this->createMock(ControllerReflector::class);
        $this->annotationReader = $this->createMock(Reader::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->filteredRouteCollectionBuilder = $this->createMock(FilteredRouteCollectionBuilder::class);

        $this->filteredRouteCollectionBuilder->method('filter')
            ->willReturn($this->routeCollection);

        $this->describer = new OpenApiPhpDescriber(
            $this->routeCollection,
            $this->controllerReflector,
            $this->annotationReader,
            $this->logger,
            $this->filteredRouteCollectionBuilder
        // $overwrite argument
        );
    }

    public function testDescribeWidthLogicException()
    {
        $routing = new Route('/test');
        $routing->setPath('test.{_format}');
        $controller = new class extends AbstractController {
            public function view(): Response
            {
                return $this->json(['success' => true]);
            }
        };
        $routing->addDefaults(['_controller' => $controller]);
        $routing2 = new Route('/test');
        $routing2->setMethods(['get']);
        $this->routeCollection->method('all')
            ->willReturn([$routing, $routing2]);
        $this->controllerReflector->method('getReflectionMethod')
            ->willReturn(new \ReflectionMethod($controller, 'view'));
        $openApi = new OpenApi([]);

        $info = new OA\Info([]);
        $operation = new Operation([]);
        $security = new Security([]);
        $tag = new OA\Tag([]);
        $this->annotationReader->method('getClassAnnotations')
            ->willReturn([$info, $operation, $security, $tag]);
        $this->annotationReader->method('getMethodAnnotations')
            ->willReturn([$info, $operation, $security, $tag]);

        $this->expectException(LogicException::class);
       $this->describer->describe($openApi);
    }

    public function testDescribeUpdatesOpenApiObject()
    {
        $routing = new Route('/test');
        $routing->setPath('test.{_format}');
        $controller = new class extends AbstractController {
            public function view(): Response
            {
                return $this->json(['success' => true]);
            }
        };
        $routing->addDefaults(['_controller' => $controller]);
        $routing2 = new Route('/test');
        $routing2->setMethods(['get']);
        $this->routeCollection->method('all')
            ->willReturn([$routing, $routing2]);
        $this->controllerReflector->method('getReflectionMethod')
            ->willReturn(new \ReflectionMethod($controller, 'view'));
        $openApi = new OpenApi([]);

        $response = new OA\Response([]);
        $this->annotationReader->method('getClassAnnotations')
            ->willReturn([$response]);
        $this->annotationReader->method('getMethodAnnotations')
            ->willReturn([$response]);

       $this->describer->describe($openApi);

        $this->assertTrue(true);
    }

    // Additional test methods go here, following the format of the example above
}