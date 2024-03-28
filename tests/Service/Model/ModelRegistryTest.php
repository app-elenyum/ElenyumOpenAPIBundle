<?php

namespace Elenyum\OpenAPI\Tests\Service\Model;

use Elenyum\OpenAPI\Service\Model\Model;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\ModelDescriber\ObjectModelDescriber;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class ModelRegistryTest extends TestCase
{
    public function testRegister()
    {
        $alternativeNames = [
            'Foo1' => [
                'type' => self::class,
                'groups' => ['group1'],
            ],
            'null' => [
                'type' => self::class,
                'groups' => ['group1'],
            ],
        ];
        $modelDescriber = $this->createMock(ObjectModelDescriber::class);

        $type = new Type(Type::BUILTIN_TYPE_NULL, false, null, true);
        $model = new Model($type, ['group1']);
        $modelDescriber->method('supports')
            ->willReturn(true);
        $modelDescriber->method('describe')
            ->willReturn(true);
        $registry = new ModelRegistry([$modelDescriber], $this->createOpenApi(), $alternativeNames);


        $this->assertEquals('#/components/schemas/null2', $registry->register($model));
        $registry->registerSchemas();
    }

    private function createOpenApi()
    {
        return new OA\OpenApi(['_context' => new Context()]);
    }
}
