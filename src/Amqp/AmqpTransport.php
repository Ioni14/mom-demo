<?php

namespace App\Amqp;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\AmqpExt\Connection;
use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class AmqpTransport implements TransportInterface
{
    private $encoder;
    private $decoder;
    private $connection;
    private $receiver;
    private $sender;

    public function __construct(EncoderInterface $encoder, DecoderInterface $decoder, Connection $connection)
    {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(callable $handler): void
    {
        dump('receive');
        ($this->receiver ?? $this->getReceiver())->receive($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        ($this->receiver ?? $this->getReceiver())->stop();
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): void
    {
        ($this->sender ?? $this->getSender())->send($envelope);
    }

    private function getReceiver(): ReceiverInterface
    {
        $this->receiver = new AmqpReceiver($this->decoder, $this->connection);
        dump($this->receiver);
        return $this->receiver;
    }

    private function getSender(): SenderInterface
    {
        return $this->sender = new AmqpSender($this->encoder, $this->connection);
    }
}
