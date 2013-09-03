<?php
namespace Oro\Bundle\SyncBundle\EventListener;

use Symfony\Component\HttpFoundation\Session\Session;

use Ratchet\Session\Serialize\PhpHandler;
use Ratchet\Session\Storage\VirtualSessionStorage;

use JDare\ClankBundle\Event\ClientEvent;

class OroWampEventListener
{
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
    public function __construct(\SessionHandlerInterface $handler, array $options = array())
    {
        $this->handler = $handler;
        $this->options = $options;
    }

    /**
     * Called whenever a client connects
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
         */
        $conn = $event->getConnection();
        $name = isset($this->options['name']) ? $this->options['name'] : ini_get('session.name');

        if ($id = $conn->WebSocket->request->getCookie($name)) {
            $storage = new VirtualSessionStorage($this->handler, $id, new PhpHandler());

            $storage->setOptions($this->options);

            $conn->Session = new Session($storage);

            $conn->Session->start();
        }

        $session = $conn->Session;
        $token   = $session->get('_security_main');

        if ($token) {
            $token = unserialize($token);

            $session->set('user', $token ? $token->getUser() : null);
        }
    }
}