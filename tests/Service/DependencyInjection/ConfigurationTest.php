<?php

namespace Elenyum\OpenAPI\Tests\Service\DependencyInjection;

use Elenyum\OpenAPI\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

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

        $this->assertArrayHasKey('cache', $config);
        $this->assertArrayHasKey('documentation', $config);
        $this->assertArrayHasKey('area', $config);

        $this->assertFalse($config['cache']['enable']);
        $this->assertNull($config['cache']['item_id']);
        $this->assertIsArray($config['documentation']);
        $this->assertIsArray($config['area']);
    }
    public function testConfigErrorTreeBuilder()
    {
        $configuration = new Configuration();

        $this->expectException(InvalidConfigurationException::class);
        $processor = new Processor();
        $processor->processConfiguration(
            $configuration,
            [$this->getErrorTestConfig()]
        );
    }

    private function getValidTestConfig()
    {
        return [
            'cache' => [
                'enable' => false,
                'item_id' => null,
            ],
            'documentation' => [
                'info' => ['title' => 'Test API'],
            ],
        ];
    }

    private function getErrorTestConfig()
    {
        return [
            'cache' => [
                'enable' => false,
                'item_id' => null,
            ],
            'documentation' => [
                'info' => ['title' => 'Test API'],
            ],
            'area' => [
                'path_patterns' => []
            ]
        ];
    }
}
