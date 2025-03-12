<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class UserRegisteredEvent extends Event
{
    public const NAME = 'user.registered';

    public function __construct()
    {
    }


}