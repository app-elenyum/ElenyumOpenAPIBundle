<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\DateTimePropertyDescriber;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class DateTimePropertyDescriberTest extends TestCase
{
    private $dateTimePropertyDescriber;

    protected function setUp(): void
    {
        $this->dateTimePropertyDescriber = new DateTimePropertyDescriber();
    }

    public function testDescribe()
    {
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, \DateTimeInterface::class);
        $property = new OA\Schema([]);

        $this->dateTimePropertyDescriber->describe([$type], $property);

        $this->assertEquals('string', $property->type);
        $this->assertEquals('date-time', $property->format);
    }

    public function testSupports()
    {
        $dateTimeType = new Type(Type::BUILTIN_TYPE_OBJECT, false, \DateTimeInterface::class);
        $unrelatedType = new Type(Type::BUILTIN_TYPE_STRING); // Some type that is not a DateTimeInterface

        $this->assertTrue($this->dateTimePropertyDescriber->supports([$dateTimeType]));
        $this->assertFalse($this->dateTimePropertyDescriber->supports([$unrelatedType]));
    }

    protected function tearDown(): void
    {
        // Your cleanup logic here
    }
}