<?php

declare(strict_types=1);

namespace App\Command;

use Enqueue\RdKafka\RdKafkaConnectionFactory;
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

        /** @see https://github.com/php-enqueue/enqueue-dev/blob/master/docs/transport/kafka.md */
        $connFactory = new RdKafkaConnectionFactory('kafka://kafka:9092');
        $context = $connFactory->createContext();

        $topic = $context->createTopic('first_topic');
        $mess = $context->createMessage('Hello World!');
//        $mess->setKey('my_key');

        $context->createProducer()->send($topic, $mess);

        $context->close();
    }
}
