<?php

namespace Oro\Bundle\SyncBundle\Twig;

use Oro\Bundle\SyncBundle\Wamp\TopicPublisher;

class OroSyncExtension extends \Twig_Extension
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

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('check_ws', array($this, 'checkWsConnected')),
        );
    }

    /**
     * Check WebSocket server connection
     *
     * @return bool True on success, false otherwise
     */
    public function checkWsConnected()
    {
        return $this->publisher->check();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'sync_extension';
    }
}
