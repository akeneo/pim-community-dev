<?php

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

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
class ArrayReader implements ItemReaderInterface, StepExecutionAwareInterface, FlushableInterface
{
    /** @var ItemReaderInterface */
    protected $reader;

    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var array */
    protected $remainingItems;

    /**
     * @param ItemReaderInterface     $reader
     * @param ArrayConverterInterface $converter
     */
    public function __construct(
        ItemReaderInterface $reader,
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
        if ($this->reader instanceof StepExecutionAwareInterface) {
            $this->reader->setStepExecution($stepExecution);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if ($this->reader instanceof FlushableInterface) {
            $this->reader->flush();
        }
    }
}
