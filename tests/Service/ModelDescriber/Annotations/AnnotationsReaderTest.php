<?php

namespace Elenyum\OpenAPI\Tests\Service\ModelDescriber\Annotations;

use Elenyum\OpenAPI\Service\ModelDescriber\Annotations\AnnotationsReader;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\ModelDescriber\Annotations\UpdateClassDefinitionResult;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

class AnnotationsReaderTest extends TestCase
{
    private $annotationsReader;
    private $modelRegistry;
    private $reader;

    protected function setUp(): void
    {
        $this->annotationsReader = null;
        $this->modelRegistry = $this->createMock(ModelRegistry::class);
        $this->reader = new AnnotationsReader($this->annotationsReader, $this->modelRegistry);
    }

    public function testUpdateDefinition()
    {
        $class = new class {
            public $property;
        };
        $reflectionClass = new ReflectionClass($class); // SomeClass should be a test dummy class
        $schema = new OA\Schema([]);

        // Assert that returned object is of the expected type
        $result = $this->reader->updateDefinition($reflectionClass, $schema);
        $this->assertInstanceOf(UpdateClassDefinitionResult::class, $result);
    }
//php ./bundle/Elenyum/ElenyumOpenAPIBundle/vendor/phpunit/phpunit/phpunit --bootstrap ./bundle/Elenyum/ElenyumOpenAPIBundle/vendor/autoload.php --configuration ./bundle/Elenyum/ElenyumOpenAPIBundle/phpunit.xml --teamcity
    public function testUpdateProperty()
    {
        $class = new class {
            public $property;
        };
        $reflectionProperty = new ReflectionProperty($class, 'property'); // SomeClass should be a test dummy class
        $property = new OA\Property([]);

        // No return value, just ensure the method is callable and does not throw errors
        $this->reader->updateProperty($reflectionProperty, $property);
        self::assertEquals(Generator::UNDEFINED, $property->property);
    }
}