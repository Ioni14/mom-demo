<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\BasicMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProduceKafkaCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('app:produce-kafka');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        dump('dispatch message');
        new BasicMessage('hello world!');

        
    }
}
