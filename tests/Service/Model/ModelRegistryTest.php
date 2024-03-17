<?php

namespace Elenyum\OpenAPI\Tests\Service\Model;

use Elenyum\OpenAPI\Service\Model\Model;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class ModelRegistryTest extends TestCase
{
    public function testNameAliasingNotAppliedForCollections()
    {
        $alternativeNames = [
            'Foo1' => [
                'type' => self::class,
                'groups' => ['group1'],
            ],
        ];
        $registry = new ModelRegistry([], $this->createOpenApi(), $alternativeNames);
        $type = new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true);

        $this->assertEquals('#/components/schemas/array', $registry->register(new Model($type, ['group1'])));
    }

    private function createOpenApi()
    {
        return new OA\OpenApi(['_context' => new Context()]);
    }
}
