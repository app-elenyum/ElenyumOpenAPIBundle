<?php

namespace Elenyum\OpenAPI\Tests\Service\Model;

use PHPUnit\Framework\TestCase;
use Elenyum\OpenAPI\Service\Model\Model;
use Symfony\Component\PropertyInfo\Type;

class ModelTest extends TestCase
{
    private $type;
    private $groups;
    private $options;
    private $serializationContext;
    private $model;

    protected function setUp(): void
    {
        $this->type = $this->createMock(Type::class);
        $this->groups = ['group1', 'group2'];
        $this->options = ['option1' => 'value1', 'option2' => 'value2'];
        $this->serializationContext = ['context1' => 'value1'];

        $this->model = new Model($this->type, $this->groups, $this->options, $this->serializationContext);
    }

    public function testGetType()
    {
        $this->assertSame($this->type, $this->model->getType());
    }

    public function testGetGroups()
    {
        $this->assertSame($this->groups, $this->model->getGroups());
    }

    public function testGetSerializationContext()
    {
        $expectedContext = $this->serializationContext;
        $expectedContext['groups'] = $this->groups;

        $this->assertSame($expectedContext, $this->model->getSerializationContext());
    }

    public function testGetHash()
    {
        // Ensure that the hash is consistent for the same Type and context
        $hash1 = $this->model->getHash();
        $hash2 = $this->model->getHash();

        $this->assertEquals($hash1, $hash2);

        // Create a new instance with the same data to see if the hash matches
        $newModel = new Model($this->type, $this->groups, $this->options, $this->serializationContext);
        $newHash = $newModel->getHash();

        $this->assertEquals($hash1, $newHash);

        // Now, create a new instance with different data and make sure the hash is different
        $differentModel = new Model($this->type, ['differentGroup'], $this->options, $this->serializationContext);
        $differentHash = $differentModel->getHash();

        $this->assertNotEquals($hash1, $differentHash);
    }

    public function testGetOptions()
    {
        $this->assertSame($this->options, $this->model->getOptions());
    }

    // Additional test methods could include scenarios for null groups and options.
}