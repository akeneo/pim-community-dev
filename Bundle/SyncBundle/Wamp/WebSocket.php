<?php

namespace Oro\Bundle\SyncBundle\Wamp;

class WebSocket
{
    /**
     * Default socket connection timeout, in seconds
     */
    const SOCKET_TIMEOUT = 2;

    /**
     * @var resource
     */
	protected $socket = null;

    /**
     * Initialize web socket connection
     *
     * @param string $host Host to connect to. Default is localhost (127.0.0.1).
     * @param int    $port Port to connect to. Default is 8080.
     */
	public function __construct($host = '127.0.0.1', $port = 8080)
	{
		$this->connect($host, $port);
	}

	public function __destruct()
	{
		$this->disconnect();
	}

    /**
     * Send raw data to a WebSocket server
     *
     * @param  string $data
     * @return string Server response
     * @throws \RuntimeException
     */
	public function sendData($data)
	{
		if (!fwrite($this->socket, "\x00" . $data . "\xff")) {
            throw new \RuntimeException('WebSocket write error');
        }

		$wsData = fread($this->socket, 2000);

		return trim($wsData,"\x00\xff");
	}

    /**
     *
     * @param  string  $host Host to connect to. Default is localhost (127.0.0.1).
     * @param  int     $port Port to connect to. Default is 8080.
     * @return boolean True on success
     * @throws \RuntimeException
     */
	protected function connect($host, $port)
	{
		$key1 = $this->generateRandomString(32);
		$key2 = $this->generateRandomString(32);
		$key3 = $this->generateRandomString(8, false, true);

		$header  = "GET /echo HTTP/1.1\r\n";
		$header .= "Upgrade: WebSocket\r\n";
		$header .= "Connection: Upgrade\r\n";
		$header .= "Host: " . $host . ":" . $port . "\r\n";
		$header .= "Sec-WebSocket-Key1: " . $key1 . "\r\n";
		$header .= "Sec-WebSocket-Key2: " . $key2 . "\r\n";
		$header .= "\r\n";
		$header .= $key3;


        $this->socket = stream_socket_client('tcp://' . $host . ':' . $port, $errno, $errstr, self::SOCKET_TIMEOUT);

        if (!$this->socket) {
            throw new \RuntimeException(sprintf('WebSocket connection error (%u): %s', $errno, $errstr));
        }

        stream_set_blocking($this->socket, false);

        // do a handshake
        if (!fwrite($this->socket, $header)) {
            throw new \RuntimeException('WebSocket write error');
        }

		/**
		 * @todo: check response here. Currently not implemented cause "2 key handshake" is already deprecated.
		 * See: http://en.wikipedia.org/wiki/WebSocket#WebSocket_Protocol_Handshake
		 */
        // $response = fread($this->socket, 2000);

		return true;
	}

	protected function disconnect()
	{
		stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
	}

    /**
     * Generate random string for a WebSocket handshake request headers
     *
     * @param  int    $length
     * @param  bool   $addSpaces
     * @param  bool   $addNumbers
     * @return string
     */
	protected function generateRandomString($length = 10, $addSpaces = true, $addNumbers = true)
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
		$useChars   = array();

		// select some random chars:
		for($i = 0; $i < $length; $i++) {
			$useChars[] = $characters[mt_rand(0, strlen($characters)-1)];
		}

		// add spaces and numbers:
		if ($addSpaces === true) {
			array_push($useChars, ' ', ' ', ' ', ' ', ' ', ' ');
		}

		if ($addNumbers === true) {
			array_push($useChars, rand(0,9), rand(0,9), rand(0,9));
		}

		shuffle($useChars);

		$randomString = trim(implode('', $useChars));

		return substr($randomString, 0, $length);
	}
}
