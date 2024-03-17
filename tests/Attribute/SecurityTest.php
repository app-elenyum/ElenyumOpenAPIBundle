<?php

namespace Elenyum\OpenAPI\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Elenyum\OpenAPI\Attribute\Security;

class SecurityTest extends TestCase
{
    public function testConstructorWithValidName()
    {
        // Тестирование конструктора с валидным именем
        $name = 'SecuritySchemeName';
        $security = new Security(name: $name);
        $this->assertEquals($name, $security->name);
    }

    public function testConstructorWithScopes()
    {
        // Тестирование конструктора с областями (scopes)
        $scopes = ['read', 'write'];
        $security = new Security(name: 'SecuritySchemeName', scopes: $scopes);
        $this->assertEquals($scopes, $security->scopes);
    }

    public function testConstructorWithAllParameters()
    {
        // Тестирование конструктора со всеми параметрами
        $name = 'SecuritySchemeName';
        $scopes = ['read', 'write'];
        $security = new Security(name: $name, scopes: $scopes);

        $this->assertEquals($name, $security->name);
        $this->assertEquals($scopes, $security->scopes);
    }
}
