<?php

namespace Oro\Bundle\BatchBundle\Item\Support;

use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;

/**
 * No operation processor that does not change anthing in the item
 *
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
