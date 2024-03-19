<?php

namespace Elenyum\OpenAPI\Tests\Service\ModelDescriber\Annotations;

use Elenyum\OpenAPI\Service\OpenApiPhp\ModelRegister;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Annotations\Reader;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\ModelDescriber\Annotations\OpenApiAnnotationsReader;
use OpenApi\Annotations\Schema;
use OpenApi\Analysis;
use OpenApi\Context;
use ReflectionClass;

class OpenApiAnnotationsReaderTest extends TestCase
{
    private $reader;
    private $modelRegistry;
    private $mediaTypes;
    private $openApiAnnotationsReader;
    private $reflectionClass;
    private $schema;

    protected function setUp(): void
    {
        $this->reader = $this->createMock(Reader::class);
        $this->modelRegistry = $this->createMock(ModelRegistry::class);
        $this->mediaTypes = ['application/json'];
        $this->reflectionClass = $this->createMock(ReflectionClass::class);
        $this->schema = $this->createMock(Schema::class);

        $this->openApiAnnotationsReader = new OpenApiAnnotationsReader(
            $this->reader,
            $this->modelRegistry,
            $this->mediaTypes
        );
    }

    public function testUpdateSchema()
    {
        $context = new Context();
        $this->schema->_context = $context;

        $modelRegister = $this->createMock(ModelRegister::class);
        $modelRegister->expects($this->never())
            ->method('__invoke')
            ->with($this->isInstanceOf(Analysis::class), $this->isInstanceOf(Context::class));

        $schemaAnnotation = $this->createMock(Schema::class);
        $this->reader->method('getClassAnnotation')->willReturn($schemaAnnotation);

        $this->openApiAnnotationsReader->updateSchema($this->reflectionClass, $this->schema);

        // Your assertions go here.
    }

    // More methods [...]
}