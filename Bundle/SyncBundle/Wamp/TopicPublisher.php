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
        if ('*' == $host) {
            $host = '127.0.0.1';
        }

        $this->host = $host;
        $this->port = (int) $port;
    }

    /**
     * Publish (broadcast) message
     *
     * @param  string       $topic Topic id (or channel), for example "acme/demo-channel"
     * @param  string|array $msg   Message
     * @return bool         True on success, false otherwise
     */
    public function send($topic, $msg)
    {
        $ws = $this->getWs();

        if (!$ws) {
            return false;
        }

        $ws->sendData(
            json_encode(
                array(
                    \Ratchet\Wamp\ServerProtocol::MSG_PUBLISH,
                    $topic,
                    $msg,
                )
            )
        );

        return true;
    }

    /**
     * Check if WebSocket server is running
     *
     * @return bool True on success, false otherwise
     */
    public function check()
    {
        return !is_null($this->getWs());
    }

    /**
     * @return WebSocket|null
     */
    protected function getWs()
    {
        if (!$this->ws) {
            try {
                $this->ws = new WebSocket($this->host, $this->port);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $this->ws;
    }
}
