<?php

declare(strict_types=1);

namespace App\Message;

class BasicMessage
{
    public $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }
}
