<?php

namespace App\Amqp;

use Symfony\Component\Messenger\Transport\AmqpExt\Connection;
use Symfony\Component\Messenger\Transport\AmqpExt\Exception\RejectMessageExceptionInterface;
use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;

class AmqpReceiver implements ReceiverInterface
{
    private $decoder;
    private $connection;
    private $shouldStop;

    public function __construct(DecoderInterface $decoder, Connection $connection)
    {
        $this->decoder = $decoder;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(callable $handler): void
    {
        while (!$this->shouldStop) {
            $AMQPEnvelope = $this->connection->get();
            if (null === $AMQPEnvelope) {
                $handler(null);

                usleep($this->connection->getConnectionCredentials()['loop_sleep'] ?? 200000);
                if (\function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }

                continue;
            }

            try {
                $handler($this->decoder->decode([
                    'body' => $AMQPEnvelope->getBody(),
                    'headers' => $AMQPEnvelope->getHeaders(),
                ]));

                $this->connection->ack($AMQPEnvelope);
            } catch (RejectMessageExceptionInterface $e) {
                $this->connection->reject($AMQPEnvelope);

                throw $e;
            } catch (\Throwable $e) {
                $this->connection->nack($AMQPEnvelope, AMQP_REQUEUE);

                throw $e;
            } finally {
                if (\function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
            }
        }
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }
}
