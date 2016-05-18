<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Buffer\BufferFactory;
use Akeneo\Component\Buffer\BufferInterface;

/**
 * Write items into a buffer and calculate headers during a flat file export
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatItemBuffer implements \Countable
{
    /** @var BufferInterface */
    protected $buffer;

    /** @var array */
    protected $headers = [];

    /** @var int */
    protected $count;

    /**
     * @param BufferFactory $bufferFactory
     */
    public function __construct(BufferFactory $bufferFactory)
    {
        $this->buffer = $bufferFactory->create();
        $this->count  = 0;
    }

    /**
     * Write an item into the buffer
     *
     * @param array $items
     * @param $addHeader
     */
    public function write(array $items, $addHeader)
    {
        foreach ($items as $item) {
            if ($addHeader) {
                $this->addToHeaders(array_keys($item));
            }

            $this->buffer->write($item);
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
     * Return the buffer
     *
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return $this->buffer;
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
