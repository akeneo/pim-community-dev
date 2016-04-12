<?php

namespace PimEnterprise\Component\CatalogRule\Connector\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;

/**
 * Don't do anything.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class DummyProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $item['sku'];
    }
}
