<?php

namespace Elenyum\OpenAPI\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Elenyum\OpenAPI\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    public function testConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $processor = new Processor();
        $config = $processor->processConfiguration(
            $configuration,
            [$this->getValidTestConfig()]
        );

        // Проверка структуры конфигурации
        $this->assertArrayHasKey('use_validation_groups', $config);
        $this->assertArrayHasKey('cache', $config);
        $this->assertArrayHasKey('documentation', $config);
        $this->assertArrayHasKey('media_types', $config);
        $this->assertArrayHasKey('areas', $config);
        $this->assertArrayHasKey('models', $config);

        // Проверка значений конфигурации
        $this->assertFalse($config['use_validation_groups']);
        $this->assertNull($config['cache']['pool']);
        $this->assertNull($config['cache']['item_id']);
        $this->assertIsArray($config['documentation']);
        $this->assertEquals(['json'], $config['media_types']);
        $this->assertArrayHasKey('default', $config['areas']);
        $this->assertFalse($config['models']['use_jms']);
        $this->assertIsArray($config['models']['names']);
    }

    private function getValidTestConfig()
    {
        return [
            'use_validation_groups' => false,
            'cache' => [
                'pool' => null,
                'item_id' => null,
            ],
            'documentation' => [
                'info' => ['title' => 'Test API'],
            ],
            'media_types' => ['json'],
            'areas' => [
                'default' => [
                    'path_patterns' => [],
                    'host_patterns' => [],
                ],
            ],
            'models' => [
                'use_jms' => false,
                'names' => [
                    'Test' => [
                        'alias' => 'TestModel',
                        'type' => 'Test\Type',
                    ],
                ],
            ],
        ];
    }
}
