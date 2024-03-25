<?php

namespace Elenyum\OpenAPI\Command;

use Elenyum\OpenAPI\Service\ApiDocGenerator;
use Elenyum\OpenAPI\Service\Render\JsonOpenApiRenderer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'elenyum:dump',
    description: 'Generated open api schema (documentation)',
    aliases: ['e:d']
)]
/** @todo сделать команду для генерации документации в open api спецификации */
class DumpOpenAPISchemaCommand extends Command
{
    public function __construct(
        private JsonOpenApiRenderer $jsonOpenApiRenderer,
        private ApiDocGenerator $apiDocGenerator,
        private string $rootPath
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate specification in OpenAPI, format: json')
            ->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'Group for documentation', null)
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'File name for save documentation', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $group = $input->getOption('group');
        $file = $input->getOption('file');

        $this->apiDocGenerator->setGroup($group);
        $spec = $this->apiDocGenerator->generate();
        $jsonDoc = $this->jsonOpenApiRenderer->render($spec);

        $io = new SymfonyStyle($input, $output);

        if ($file !== null) {
            file_put_contents($this->rootPath.'/'.$file.'.json', $jsonDoc);
        } else {
            $output->writeln($jsonDoc, OutputInterface::OUTPUT_RAW);
        }

        $io->success('Schema generated.');

        return Command::SUCCESS;
    }
}