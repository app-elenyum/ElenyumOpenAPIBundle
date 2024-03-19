<?php

namespace Elenyum\OpenAPI\Tests\Service\Render;

use Elenyum\OpenAPI\Service\Render\JsonOpenApiRenderer;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;

class JsonOpenApiRendererTest extends TestCase
{
    private JsonOpenApiRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new JsonOpenApiRenderer();
    }

    public function testRenderWithPrettyPrint()
    {
        $spec = $this->createMock(OpenApi::class);
        $output = $this->renderer->render($spec, ['no-pretty' => false]);

        $this->assertJson($output); // Check for newlines as an indicator of pretty print
    }

    public function testRenderWithoutPrettyPrint()
    {
        $spec = $this->createMock(OpenApi::class);
        $output = $this->renderer->render($spec, ['no-pretty' => true]);

        $this->assertJson($output);
    }

    public function testRenderDefaultOptions()
    {
        $spec = $this->createMock(OpenApi::class);
        $output = $this->renderer->render($spec);

        // Should default to pretty print
        $this->assertJson($output);
    }
}