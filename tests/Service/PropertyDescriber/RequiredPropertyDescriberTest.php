<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\RequiredPropertyDescriber;
use Elenyum\OpenAPI\Service\PropertyDescriber\PropertyDescriberInterface;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;

class RequiredPropertyDescriberTest extends TestCase
{
    private $requiredPropertyDescriber;
    private $decoratedDescriber;

    protected function setUp(): void
    {
        $this->decoratedDescriber = $this->createMock(PropertyDescriberInterface::class);
        $this->requiredPropertyDescriber = new RequiredPropertyDescriber();
        $this->requiredPropertyDescriber->setPropertyDescriber($this->decoratedDescriber);
    }

    public function testDescribeSetsPropertyAsRequired()
    {
        $types = []; // Would normally be populated with Type instances
        $property = new OA\Property([]);
        $property->property = 'testProperty';
        $schema = new OA\Schema([]);

        $this->decoratedDescriber->expects($this->any())
            ->method('describe');

        $this->requiredPropertyDescriber->describe($types, $property, null, $schema, []);

        $this->assertContains('testProperty', $schema->required);
    }

    public function testDescribeWithNullablePropertyDoesNotSetAsRequired()
    {
        $types = []; // Would normally be populated with Type instances
        $property = new OA\Property([]);
        $property->property = 'testProperty';
        $property->nullable = true;
        $schema = new OA\Schema([]);

        $this->decoratedDescriber->expects($this->any())
            ->method('describe');

        $this->requiredPropertyDescriber->describe($types, $property, null, $schema, []);

        $this->assertNotContains('testProperty', [$schema->required]);
    }

    public function testDescribeWithDefaultPropertyDoesNotSetAsRequired()
    {
        $types = []; // Would normally be populated with Type instances
        $property = new OA\Property([]);
        $property->property = 'testProperty';
        $property->default = 'defaultValue';
        $schema = new OA\Schema([]);

        $this->decoratedDescriber->expects($this->any())
            ->method('describe');

        $this->requiredPropertyDescriber->describe($types, $property, null, $schema, []);

        $this->assertNotContains('testProperty', [$schema->required]);
    }

    public function testSupports()
    {
        $this->assertTrue($this->requiredPropertyDescriber->supports([]));
    }
}