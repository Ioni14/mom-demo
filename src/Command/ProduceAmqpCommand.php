<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\BasicMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProduceAmqpCommand extends Command
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();
        $this->bus = $bus;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:produce-amqp');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        dump('dispatch message');
        $this->bus->dispatch(new BasicMessage('hello world!'));
    }
}
