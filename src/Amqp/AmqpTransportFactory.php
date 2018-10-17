<?php

namespace App\Amqp;

use Symfony\Component\Messenger\Transport\AmqpExt\Connection;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class AmqpTransportFactory implements TransportFactoryInterface
{
    private $encoder;
    private $decoder;
    private $debug;

    public function __construct(EncoderInterface $encoder, DecoderInterface $decoder, bool $debug)
    {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->debug = $debug;
    }

    public function createTransport(string $dsn, array $options): TransportInterface
    {
        return new AmqpTransport($this->encoder, $this->decoder, Connection::fromDsn($dsn, $options, $this->debug));
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'app-amqp://');
    }
}
