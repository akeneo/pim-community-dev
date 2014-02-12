<?php

namespace Akeneo\Bundle\BatchBundle\Item\Support;

use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;

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
