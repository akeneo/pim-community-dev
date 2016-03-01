<?php

namespace Pim\Component\Connector\Writer\File\Product;

use Akeneo\Component\Buffer\BufferFactory;
use Akeneo\Component\Buffer\BufferInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatRowBuffer
{
    /** @var BufferInterface */
    private $buffer;
    /** @var array */
    private $headers;

    public function __construct(BufferFactory $bufferFactory)
    {
        $this->headers = [];
        $this->buffer = $bufferFactory->create();
    }

    /**
     * @param array $items
     * @param bool  $addHeader
     */
    public function write(array $items, $addHeader)
    {
        foreach ($items as $item) {
            $product = $item['product'];
            if ($addHeader) {
                $this->addToHeaders(array_keys($product));
            }

            $this->buffer->write($product);
        }
    }

    /**
     * @return BufferInterface
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
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

        $identifier = array_shift($headers);
        natsort($headers);
        array_unshift($headers, $identifier);

        $this->headers = $headers;
    }
}
