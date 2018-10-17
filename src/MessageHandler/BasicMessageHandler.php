<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\BasicMessage;
use Symfony\Component\Messenger\Transport\AmqpExt\Exception\RejectMessageExceptionInterface;

class BasicMessageHandler
{
    public function __construct()
    {
    }

    public function __invoke(BasicMessage $message)
    {
        dump($message);

//        throw new RejectMessageException("Osef de ton message !");
    }
}

class RejectMessageException extends \Exception implements RejectMessageExceptionInterface
{
}
