<?php

namespace Akeneo\Bundle\BatchBundle\Item\Support;

use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;

/**
 * Very basic sample transformer that will put the first letter of each item in uppercase
 *
 */
class UcfirstProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return ucfirst($item);
    }
}
