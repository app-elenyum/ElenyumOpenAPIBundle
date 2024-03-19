<?php

namespace Elenyum\OpenAPI\Tests\Service\ModelDescriber;

use Elenyum\OpenAPI\Service\Model\Model;
use Elenyum\OpenAPI\Service\ModelDescriber\EnumModelDescriber;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;


// Mocked enums for the test
enum UnitEnumExample: string
{
    case VALUE_ONE = 'VALUE_ONE';
    case VALUE_TWO = 'VALUE_TWO';
}

enum BackedEnumExample: int
{
    case VALUE_ONE = 1;
    case VALUE_TWO = 2;
}

final class EnumModelDescriberTest extends TestCase
{
    public function testDescribeWithStringBackedEnum()
    {
        $enumClass = UnitEnumExample::class;
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, $enumClass);
        $model = new Model($type);
        $schema = new Schema([]);

        $modelDescriber = new EnumModelDescriber();
        $modelDescriber->describe($model, $schema);

        $this->assertSame('string', $schema->type);
        $this->assertSame(['VALUE_ONE', 'VALUE_TWO'], $schema->enum);
    }

    public function testDescribeWithIntBackedEnum()
    {
        $enumClass = BackedEnumExample::class;
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, $enumClass);
        $model = new Model($type);
        $schema = new Schema([]);

        $modelDescriber = new EnumModelDescriber();
        $modelDescriber->describe($model, $schema);

        $this->assertSame('integer', $schema->type);
        $this->assertSame([1, 2], $schema->enum);
    }

    public function testSupportsEnum()
    {
        $enumClass = BackedEnumExample::class; // Assuming this is a string-backed enum for this case
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, $enumClass);
        $model = new Model($type);

        $modelDescriber = new EnumModelDescriber();

        $this->assertTrue($modelDescriber->supports($model));
    }

    public function testDoesNotSupportsNonEnum()
    {
        $nonEnumClass = \NonEnum::class; // Not an enum class
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, $nonEnumClass);
        $model = new Model($type);

        $modelDescriber = new EnumModelDescriber();

        $this->assertFalse($modelDescriber->supports($model));
    }
}
