<?php
namespace Oro\SyncBundle\Server;

use Ratchet\ConnectionInterface as Conn;
use Ratchet\Wamp\WampServerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class Sync implements WampServerInterface
{
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    public function onPublish(Conn $conn, $topic, $event, array $exclude = array(), array $eligible = array())
    {
        // clients are not allowed to publish events
        $conn->close();
    }

    public function onCall(Conn $conn, $id, $topic, array $params)
    {
        // clients are not allowed to call RPC
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onOpen(Conn $conn)
    {
    }

    public function onClose(Conn $conn)
    {
    }

    public function onSubscribe(Conn $conn, $topic)
    {
        if (!true /* check if user has permission to be subscribed on */) {
            $topic->remove($conn);
            return;
        }

        // When a visitor subscribes to a topic link the Topic object in a  lookup array
        if (!array_key_exists($topic->getId(), $this->subscribedTopics)) {
            $this->subscribedTopics[$topic->getId()] = $topic;
        }
    }

    public function onUnSubscribe(Conn $conn, $topic)
    {
        if (!count($this->subscribedTopics[$topic->getId()])) {
            unset($this->subscribedTopics[$topic->getId()]);
        }
    }

    public function onError(Conn $conn, \Exception $e)
    {
    }

    public function onUpdate($message)
    {
        $message = json_decode($message, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!empty($message['url'])
            && $message['attributes']
            && !array_key_exists($message['url'], $this->subscribedTopics)
        ) {
            return;
        }

        // re-send the data to all the clients subscribed to that category
        $this->subscribedTopics[$message['url']]->broadcast($message['attributes']);
    }
}
