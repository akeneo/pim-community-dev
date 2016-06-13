<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Reader\Csv;


use Doctrine\ORM\EntityManager;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\Csv\Reader;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;

/**
 * The Tag Reader allows to read file CSV file containing 'tags' column, then return tag one by one.
 * This class is stateful because it contains the next items to return.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class TagReader extends Reader
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
