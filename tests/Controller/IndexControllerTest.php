<?php

namespace Elenyum\OpenAPI\Tests\Controller;

use Elenyum\OpenAPI\Controller\IndexController;
use Elenyum\OpenAPI\Service\ApiDocGenerator;
use Elenyum\OpenAPI\Service\Render\JsonOpenApiRenderer;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Elenyum\OpenAPI\Exception\RenderInvalidArgumentException;

class IndexControllerTest extends TestCase
{
    private $jsonOpenApiRenderer;
    private $apiDocGenerator;
    private $controller;

    protected function setUp(): void
    {
        $this->jsonOpenApiRenderer = $this->createMock(JsonOpenApiRenderer::class);
        $this->apiDocGenerator = $this->createMock(ApiDocGenerator::class);

        $this->controller = new IndexController(
            $this->jsonOpenApiRenderer,
            $this->apiDocGenerator
        );
    }

    public function testInvokeWithValidArea()
    {
        $request = new Request();
        $specData = new OpenApi([]);
        $renderedData = '{}';

        // Настройка ожидаемого поведения макетов
        $this->apiDocGenerator
            ->method('generate')
            ->willReturn($specData);

        $this->jsonOpenApiRenderer
            ->method('render')
            ->with($specData)
            ->willReturn($renderedData);

        $response = $this->controller->__invoke($request, 'default');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::fromJsonString($renderedData), $response);
    }

    public function testInvokeWithUnsupportedArea()
    {
        $request = new Request();
        $unsupportedArea = 'unsupported_area';

        $this->apiDocGenerator
            ->method('generate')
            ->will($this->throwException(new RenderInvalidArgumentException()));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Area is not supported as it isn\'t bad config.');

        $this->controller->__invoke($request, $unsupportedArea);
    }
}
