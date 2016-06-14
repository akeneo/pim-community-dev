<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Connector\Reader\File\Xlsx;

use Doctrine\ORM\EntityManager;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\Xlsx\Reader;

/**
 * The Array Reader allows to read file XLSX file, where each line will return an array of items. The main function
 * will return elements one by one.
 * This class is stateful because it contains the next items to return.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
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
