<?php

namespace Attribute;

use PHPUnit\Framework\TestCase;
use Elenyum\OpenAPI\Attribute\Model;

class ModelTest extends TestCase
{
    public function testConstructorWithValidType()
    {
        // Тестирование конструктора с валидным типом
        $type = 'exampleType';
        $model = new Model(type: $type);
        $this->assertEquals($type, $model->type);
    }

    public function testConstructorWithGroups()
    {
        // Тестирование конструктора с группами
        $groups = ['group1', 'group2'];
        $model = new Model(type: 'exampleType', groups: $groups);
        $this->assertEquals($groups, $model->groups);
    }

    public function testConstructorWithOptions()
    {
        // Тестирование конструктора с опциями
        $options = ['option1' => 'value1', 'option2' => 'value2'];
        $model = new Model(type: 'exampleType', options: $options);
        $this->assertEquals($options, $model->options);
    }

    public function testConstructorWithSerializationContext()
    {
        // Тестирование конструктора с контекстом сериализации
        $serializationContext = ['context1' => 'value1', 'context2' => 'value2'];
        $model = new Model(type: 'exampleType', serializationContext: $serializationContext);
        $this->assertEquals($serializationContext, $model->serializationContext);
    }

    public function testConstructorWithAllParameters()
    {
        // Тестирование конструктора со всеми параметрами
        $type = 'exampleType';
        $groups = ['group1', 'group2'];
        $options = ['option1' => 'value1', 'option2' => 'value2'];
        $serializationContext = ['context1' => 'value1', 'context2' => 'value2'];
        $model = new Model(type: $type, groups: $groups, options: $options, serializationContext: $serializationContext);

        $this->assertEquals($type, $model->type);
        $this->assertEquals($groups, $model->groups);
        $this->assertEquals($options, $model->options);
        $this->assertEquals($serializationContext, $model->serializationContext);
    }
}