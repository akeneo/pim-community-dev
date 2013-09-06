<?php

namespace Oro\Bundle\SyncBundle\EventListener;

use Oro\Bundle\SyncBundle\Wamp\TopicPublisher;

class OroMaintenanceEventListener
{
    /**
     * @var TopicPublisher
     */
    protected $publisher;

    /**
     * @param TopicPublisher $publisher
     */
    public function __construct(TopicPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function onModeOn()
    {
        $this->publisher->send('oro/maintenance', array('isOn' => true, 'msg' => 'Maintenance mode is ON'));
    }

    public function onModeOff()
    {
        $this->publisher->send('oro/maintenance', array('isOn' => false, 'msg' => 'Maintenance mode is OFF'));
    }
}