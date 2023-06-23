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
    private int $count = 0;

    public function __construct(?string $filePath = null)
    {
        parent::__construct($filePath);
        if ($filePath) {
            $file = new \SplFileObject($filePath, 'a+');
            $file->seek(PHP_INT_MAX);
            $this->count = $file->key();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($items, array $options = [])
    {
        foreach ($items as $item) {
            if (isset($options['withHeader']) && $options['withHeader']) {
                $this->addToHeaders(array_keys($item));
            }

            $this->count++;
            parent::write($item, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
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
