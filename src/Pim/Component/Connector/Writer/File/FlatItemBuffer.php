<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Buffer\BufferInterface;
use Akeneo\Component\Buffer\JSONFileBuffer;

/**
 * Puts items into a buffer and calculate headers during a flat file export
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatItemBuffer extends JSONFileBuffer implements BufferInterface, \Countable
{
    /** @var BufferInterface */
    protected $buffer;

    /** @var array */
    protected $headers = [];

    /** @var int */
    protected $count;

    public function __construct()
    {
        parent::__construct();

        $this->count = 0;
    }

    /**
     * Write an item into the buffer
     *
     * @param array $items
     */
    public function write($items)
    {
        foreach ($items as $item) {
            $this->addToHeaders(array_keys($item));

            parent::write($item);
            $this->count++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Return the headers of every columns
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Add the specified keys to the list of headers
     *
     * @param array $keys
     */
    protected function addToHeaders(array $keys)
    {
        $headers = array_merge($this->headers, $keys);
        $headers = array_unique($headers);

        $this->headers = $headers;
    }
}
