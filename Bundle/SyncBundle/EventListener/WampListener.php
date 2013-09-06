<?php

namespace Oro\Bundle\SyncBundle\EventListener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Ratchet\Session\Serialize\PhpHandler;
use Ratchet\Session\Storage\VirtualSessionStorage;

use JDare\ClankBundle\Event\ClientEvent;

class OroWampEventListener
{
    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * Session options
     *
     * @var array
     */
    protected $options;

    /**
     * @param \SessionHandlerInterface $handler
     */
    public function __construct(\SessionHandlerInterface $handler, SecurityContextInterface $security, array $options = array())
    {
        $this->handler  = $handler;
        $this->security = $security;
        $this->options  = $options;
    }

    /**
     * Called whenever a client connects.
     * This will add $security property for a connection representing security context for a logged user (if any).
     *
     * @param ClientEvent $event
     */
    public function onClientConnect(ClientEvent $event)
    {
        /*
         * $conn has following properties:
         *  - resourceId
         *  - remoteAddress
         *  - WebSocket
         *  - Session
         *  - WAMP
         *
         * New $security property added (instance of SecurityContextInterface)
         */
        $conn = $event->getConnection();
        $name = isset($this->options['name']) ? $this->options['name'] : ini_get('session.name');

        // can't use security context application/server wide, it should be unique per connection
        $conn->security = clone $this->security;

        if ($id = $conn->WebSocket->request->getCookie($name)) {
            $storage = new VirtualSessionStorage($this->handler, $id, new PhpHandler());

            $storage->setOptions($this->options);

            $conn->Session = new Session($storage);

            $conn->Session->start();

            foreach ($conn->Session->all() as $key => $val) {
                if (preg_match('/security_(.+)$/', $key)) {
                    $token = unserialize($val);

                    if ($token instanceof TokenInterface) {
                        $conn->security->setToken($token);

                        return;
                    }
                }
            }
        }
    }
}