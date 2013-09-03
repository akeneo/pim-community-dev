<?php

namespace Oro\Bundle\SyncBundle\Wamp;

class TopicPublisher
{
    /**
     * Web socket server host
     *
     * @var string
     */
    protected $host;

    /**
     * Web socket server port
     *
     * @var int
     */
    protected $port;

    /**
     * @var WebSocket
     */
    protected $ws = null;

    /**
     *
     * @param string $host Host to connect to. Default is localhost (127.0.0.1).
     * @param int    $port Port to connect to. Default is 8080.
     */
    public function __construct($host = '127.0.0.1', $port = 8080)
    {
        $this->host = $host;
        $this->port = (int) $port;
    }

    /**
     * Publish (broadcast) message
     *
     * @param string       $topic Topic id (or channel), for example "acme/demo-channel"
     * @param string|array $msg   Message
     */
    public function send($topic, $msg)
    {
        $this
            ->getWs()
            ->sendData(
                json_encode(
                    array(
                        \Ratchet\Wamp\ServerProtocol::MSG_PUBLISH,
                        $topic,
                        $msg,
                    )
                )
            );
    }

    /**
     * @return WebSocket
     */
    protected function getWs()
    {
        if (!$this->ws) {
            $this->ws = new WebSocket($this->host, $this->port);
        }

        return $this->ws;
    }
}