<?php

namespace Elenyum\OpenAPI\Tests\Service\OpenApiPhp;

use Elenyum\OpenAPI\Attribute\Model as ModelAnnotation;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\OpenApiPhp\ModelRegister;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;

class ModelRegisterTest extends TestCase
{
    private $modelRegistry;
    private $modelRegister;

    protected function setUp(): void
    {
        $this->modelRegistry = $this->createMock(ModelRegistry::class);
        $this->modelRegister = new ModelRegister($this->modelRegistry, ['json']);
    }

    public function testInvokeWithNestedModelSchema()
    {
        $context = new Context(['version' => '1.0.0']);
        $analysis = new Analysis([], $context);
        $schema = new OA\Schema([]);
        $response = new OA\Response([]);
        $modelAnnotation = new ModelAnnotation(['type' => 'MyModel']);

        $response->attachables = [$modelAnnotation];
        // Assuming $schema would internally use annotation as a `$ref`
        $schema->ref = $modelAnnotation;
        $analysis->annotations->attach($schema);
        $analysis->annotations->attach($response);

        // Setup expected behavior of the mock ModelRegistry
        $this->modelRegistry->expects($this->exactly(2))
            ->method('register')
            ->willReturn('MyModelRef');

        ($this->modelRegister)($analysis);

        // Asserts
        $this->assertEquals('MyModelRef', $schema->ref);
    }

    // Additional tests to cover other scenarios...

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}