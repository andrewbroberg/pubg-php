<?php

namespace AndrewBroberg\PUBG;

use Exception;

class TooManyRequestsException extends Exception
{
    public $rateLimit;
    public $remaining;
    public $resetAt;

    public function __construct($rateLimit, $remaining, $resetAt)
    {
        $this->rateLimit = $rateLimit;
        $this->remaining = $remaining;
        $this->resetAt = $resetAt;

        parent::__construct();
    }
}