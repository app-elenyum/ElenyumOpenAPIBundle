<?php

namespace Elenyum\OpenAPI\Tests\Command;

use Elenyum\OpenAPI\Command\DumpOpenAPISchemaCommand;
use Elenyum\OpenAPI\Service\ApiDocGenerator;
use Elenyum\OpenAPI\Service\Render\JsonOpenApiRenderer;
use OpenApi\Annotations\OpenApi;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

class DumpOpenAPISchemaCommandTest extends TestCase
{
    private $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testExecuteWithoutFileOption(): void
    {
        $apiDocGeneratorMock = $this->createMock(ApiDocGenerator::class);
        $apiDocGeneratorMock->method('setGroup');
        $openApi = new OpenApi([]);
        $apiDocGeneratorMock->method('generate')->willReturn($openApi);

        $jsonOpenApiRendererMock = $this->createMock(JsonOpenApiRenderer::class);
        $jsonOpenApiRendererMock->method('render')->willReturn('{}');

        $command = new DumpOpenAPISchemaCommand($jsonOpenApiRendererMock, $apiDocGeneratorMock, vfsStream::url('root'));
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('elenyum:dump'));

        $this->assertEquals(Command::SUCCESS, $commandTester->execute([
            '--group' => 'test'
        ]));

        $this->assertStringContainsString('{}', $commandTester->getDisplay());
    }

    public function testExecuteWithFileOption(): void
    {
        $apiDocGeneratorMock = $this->createMock(ApiDocGenerator::class);
        $apiDocGeneratorMock->method('setGroup');
        $openApi = new OpenApi([]);
        $apiDocGeneratorMock->method('generate')->willReturn($openApi);

        $jsonOpenApiRendererMock = $this->createMock(JsonOpenApiRenderer::class);
        $jsonOpenApiRendererMock->method('render')->willReturn('{}');

        $command = new DumpOpenAPISchemaCommand($jsonOpenApiRendererMock, $apiDocGeneratorMock, vfsStream::url('root'));
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('elenyum:dump'));

        $commandTester->execute([
            '--group' => 'test',
            '--file' => 'api'
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertTrue($this->root->hasChild('api.json'));
    }
}