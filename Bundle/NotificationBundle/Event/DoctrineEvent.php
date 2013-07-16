<?php

namespace Oro\Bundle\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class DoctrineEvent extends Event
{
    /**
     * Event arguments
     *
     * @var
     */
    protected $args;

    public function __construct()
    {
        $this->args = func_get_args();
    }
}
