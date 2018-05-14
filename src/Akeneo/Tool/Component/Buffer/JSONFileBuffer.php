<?php

namespace Akeneo\Tool\Component\Buffer;

use Akeneo\Tool\Component\Buffer\Exception\UnsupportedItemTypeException;

/**
 * Implementation of BufferInterface embedding a SplFileObject where each item is JSON-encoded on a separate line.
 * The buffer file is created at instantiation and deleted at destruction. FIFO behavior.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class JSONFileBuffer implements BufferInterface
{
    const FILE_PREFIX = 'akeneo_buffer_';

    /** @var string */
    protected $filename;

    /** @var \SplFileObject */
    protected $file;

    /**
     * Create file at buffer instantiation
     */
    public function __construct()
    {
        $this->filename = tempnam(sys_get_temp_dir(), self::FILE_PREFIX);
        $this->file = new \SplFileObject($this->filename, 'r+');

        $this->file->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);
    }

    /**
     * Close and delete file at buffer destruction
     */
    public function __destruct()
    {
        unset($this->file);
        if (is_file($this->filename)) {
            unlink($this->filename);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($item, array $options = [])
    {
        if (!is_array($item) && !is_scalar($item)) {
            throw new UnsupportedItemTypeException(
                sprintf('%s only supports items of type scalar or array', __CLASS__)
            );
        }

        $this->file->fwrite(json_encode($item) . PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $rawLine = $this->file->current();

        return json_decode($rawLine, true);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->file->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->file->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->file->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->file->rewind();
    }
}
