<?php

namespace Elenyum\OpenAPI\Tests\Service\Describer;

use Elenyum\OpenAPI\Service\Describer\DefaultDescriber;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;

class DefaultDescriberTest extends TestCase
{
    private $describer;

    protected function setUp(): void
    {
        $options = [
            // Mock options as per your configuration
            'documentation' => [
                'info' => [
                    'title' => 'Test API',
                    'version' => '1.0.0',
                    'description' => 'An API description here'
                ]
            ]
        ];

        $this->describer = new DefaultDescriber($options);
    }

    public function testDescribeAddsInfoToOpenApi()
    {
        $api = new OA\OpenApi([]);

        // Provide the initial state of $api without an Info instance
        $this->assertEquals(Generator::UNDEFINED, $api->info);

        // Describe the $api using the describer
        $this->describer->describe($api);

        // Check that Info instance has been added and filled with options
        $this->assertInstanceOf(OA\Info::class, $api->info);
        $this->assertEquals('Test API', $api->info->title);
        $this->assertEquals('1.0.0', $api->info->version);
        $this->assertEquals('An API description here', $api->info->description);
    }

    // More test methods should be added to test other functionalities.
    // E.g., ensuring responses are properly added to operations when undefined, etc.
}