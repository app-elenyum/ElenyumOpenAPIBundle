<?php

namespace Elenyum\OpenAPI\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'Elenyum:GenerateOpenAPISchema',
    description: 'Generated open api schema (documentation)',
    aliases: ['e:g']
)]
/** @todo сделать команду для генерации документации в open api спецификации */
class GenerateOpenAPISchemaCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->success('Schema generated.');

        return Command::SUCCESS;
    }
}