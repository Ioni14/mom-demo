<?php

declare(strict_types=1);

namespace App\Command;

use Enqueue\RdKafka\RdKafkaConnectionFactory;
use Interop\Queue\PsrMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeKafkaCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('app:consume-kafka');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        dump('consuming messages...');

        /** @see https://github.com/php-enqueue/enqueue-dev/blob/master/docs/transport/kafka.md#consume-message */
        $connFactory = new RdKafkaConnectionFactory([
            'global' => [
                'group.id' => 'my-demo-app',
                'metadata.broker.list' => 'kafka:9092',
                'enable.auto.commit' => 'false',
            ],
//            'topic' => [
//                'auto.offset.reset' => 'beginning',
//            ],
        ]);
        $psrContext = $connFactory->createContext();
        $topic = $psrContext->createTopic('dummyTopic');
        $consumer = $psrContext->createConsumer($topic);
        $consumer->setCommitAsync(true); // better perfs

        while (true) {
            $message = $consumer->receive();
            if (null === $message) {
                usleep(200000);
                if (\function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }

                continue;
            }

            $this->handleMessage($message);
            $consumer->acknowledge($message);

            if (\function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }

        $psrContext->close();
    }

    /**
     * TODO : extract to a MessageHandler.
     *
     * @param PsrMessage $message
     */
    private function handleMessage(PsrMessage $message): void
    {
        dump('handle message', $message);
    }
}
