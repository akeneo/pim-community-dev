<?php

namespace Pim\Component\Connector\Reader\File\Xlsx;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;

/**
 * The Array Reader allows to read file XLSX file, where cells contains an array of items. The main function will return
 * elements one by one.
 * This class is stateful because it contains the next items to return.
 *
 * For example, a XLSX asset configurations file contains lines like this:
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
class ArrayReader extends Reader
{
    /** @var array */
    protected $remainingItems;

    /**
     * {@inheritdoc}
     */
    public function __construct(FileIteratorFactory $fileIteratorFactory, ArrayConverterInterface $converter)
    {
        parent::__construct($fileIteratorFactory, $converter);

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

        $items = parent::read();
        if (null !== $items) {
            $this->remainingItems = $items;

            return $this->read();
        }

        return null;
    }
}
