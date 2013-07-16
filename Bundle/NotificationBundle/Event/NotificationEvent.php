<?php

namespace Oro\Bundle\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class NotificationEvent extends Event
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
        $this->args['entity'] = isset($this->args[1]) ? $this->args[1] : false;
    }

    public function getEntity()
    {
        return isset($this->args['entity']) ? $this->args['entity'] : false;
    }
}
