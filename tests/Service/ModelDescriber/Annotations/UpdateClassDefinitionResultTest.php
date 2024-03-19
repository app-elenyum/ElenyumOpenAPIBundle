<?php

namespace Elenyum\OpenAPI\Tests\Service\ModelDescriber\Annotations;

use Elenyum\OpenAPI\Service\ModelDescriber\Annotations\UpdateClassDefinitionResult;
use PHPUnit\Framework\TestCase;

class UpdateClassDefinitionResultTest extends TestCase
{
    public function testShouldDescribeModelProperties()
    {
        $resultTrue = new UpdateClassDefinitionResult(true);
        $this->assertTrue($resultTrue->shouldDescribeModelProperties());

        $resultFalse = new UpdateClassDefinitionResult(false);
        $this->assertFalse($resultFalse->shouldDescribeModelProperties());
    }
}