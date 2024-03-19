<?php

namespace Elenyum\OpenAPI\Tests\Service\DependencyInjection;

use Elenyum\OpenAPI\DependencyInjection\ElenyumOpenAPIExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class ElenyumOpenAPIExtensionTest extends TestCase
{
    private $container;
    private $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ElenyumOpenAPIExtension();
    }

    public function testLoadExtension()
    {
        $configs = [
            'elenyum_open_api' => [
                // Здесь должны быть ваши тестовые конфигурационные данные
            ]
        ];

        $this->extension->load($configs, $this->container);

        // Проверяем, что параметры присутствуют в контейнере
        $this->assertTrue($this->container->hasParameter('elenyum_open_api.config'));

        // Проверяем что конфигурация провалидированная
        $config = $this->container->getParameter('elenyum_open_api.config');
        $this->assertIsArray($config);

        // Проверяем, что загрузчик добавил необходимые ресурсы

        $resources = $this->container->getResources();
        $this->assertInstanceOf(FileResource::class, $resources[0]);
        $this->assertStringContainsString('services.yaml', (string) $resources[0]);
    }

    // Здесь могут быть другие методы тестирования для проверки различных аспектов расширения
}
