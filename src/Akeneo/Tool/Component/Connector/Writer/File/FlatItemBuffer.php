<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Buffer\BufferInterface;
use Akeneo\Tool\Component\Buffer\JSONFileBuffer;

/**
 * Puts items into a buffer and calculate headers during a flat file export
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatItemBuffer extends JSONFileBuffer implements BufferInterface, \Countable
{
    /** @var array */
    protected $headers = [];

    /** @var int */
    protected $count = 0;

    /**
     * {@inheritdoc}
     */
    public function write($items, array $options = [])
    {
        foreach ($items as $item) {
            if (isset($options['withHeader']) && $options['withHeader']) {
                $this->addToHeaders(array_keys($item));
            }

            parent::write($item, $options);
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
    public function addToHeaders(array $keys)
    {
        $headers = array_merge($this->headers, $keys);
        $headers = array_unique($headers);

        $this->headers = $headers;
    }
}
