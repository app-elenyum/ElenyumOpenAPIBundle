<?php

namespace Attribute;

use PHPUnit\Framework\TestCase;
use Elenyum\OpenAPI\Attribute\Areas;

class AreasTest extends TestCase
{
    public function testConstructorValidAreas()
    {
        // Тестирование конструктора с валидным массивом областей
        $areas = ['area1', 'area2'];
        $areasAttribute = new Areas(['value' => $areas]);
        $this->assertTrue($areasAttribute->has('area1'));
        $this->assertTrue($areasAttribute->has('area2'));
        $this->assertFalse($areasAttribute->has('area3'));
    }

    public function testConstructorInvalidArgument()
    {
        // Ошибка должна быть сгенерирована если область не строка
        $areas = [1, 'area2'];
        $this->expectException(\InvalidArgumentException::class);
        new Areas(['value' => $areas]);
    }

    public function testConstructorNoAreas()
    {
        // Ошибка должна быть сгенерирована если массив областей пустой
        $areas = [];
        $this->expectException(\InvalidArgumentException::class);
        new Areas(['value' => $areas]);
    }

    public function testHasMethod()
    {
        // Проверка наличия области
        $areas = new Areas(['value' => ['area1']]);
        $this->assertTrue($areas->has('area1'));
        $this->assertFalse($areas->has('area2'));
    }

    public function testConstructorDuplicateAreas()
    {
        // Игнорирование дублирующихся значений областей
        $areas = ['area1', 'area1', 'area2'];
        $areasAttribute = new Areas(['value' => $areas]);
        $this->assertTrue($areasAttribute->has('area1'));
        $this->assertTrue($areasAttribute->has('area2'));
    }

    public function testWithoutValue()
    {
        // Проверка наличия области
        $areas = new Areas(['area1']);
        $this->assertTrue($areas->has('area1'));
        $this->assertFalse($areas->has('area2'));
    }
}

?>