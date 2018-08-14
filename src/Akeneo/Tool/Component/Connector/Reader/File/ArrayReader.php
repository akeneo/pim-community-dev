<?php

namespace Akeneo\Tool\Component\Connector\Reader\File;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * The Array Reader allows to read file, where cells contains an array of items. The main function will return
 * elements one by one.
 * This class is stateful because it contains the next items to return.
 *
 * For example, an asset configurations file contains lines like this:
 * code;tags
 * asset_code;car,boat,shoes
 *
 * The ArrayReader will read line by line but each call of read() will return tag elements one by one:
 * - car
 * - boat
 * - shoes
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArrayReader implements FileReaderInterface
{
    /** @var FileReaderInterface */
    protected $reader;

    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var array */
    protected $remainingItems;

    /**
     * @param FileReaderInterface     $reader
     * @param ArrayConverterInterface $converter
     */
    public function __construct(
        FileReaderInterface $reader,
        ArrayConverterInterface $converter
    ) {
        $this->reader = $reader;
        $this->converter = $converter;

        $this->remainingItems = [];
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (count($this->remainingItems) > 0) {
            $item = array_shift($this->remainingItems);

            return $item;
        }

        $items = $this->reader->read();
        if (null !== $items) {
            $this->remainingItems = $this->converter->convert($items);

            return $this->read();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->reader->setStepExecution($stepExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->reader->flush();
    }
}
