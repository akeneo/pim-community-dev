<?php

namespace Pim\Bundle\BatchBundle\Item\Support;

use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;

/**
 * No operation processor that does not change anthing in the item
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NoopProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $item;
    }
}
