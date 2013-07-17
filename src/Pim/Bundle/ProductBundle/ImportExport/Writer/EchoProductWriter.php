<?php

namespace Pim\Bundle\ProductBundle\ImportExport\Writer;

use Pim\Bundle\BatchBundle\Item\ItemWriterInterface;

/**
 * Debugging writer that display some columns of the product
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class EchoProductWriter implements ItemWriterInterface
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            echo $item->getSku()."\n";
        }
    }
}
