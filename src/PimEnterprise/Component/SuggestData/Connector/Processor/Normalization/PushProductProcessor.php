<?php
declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Connector\Processor\Normalization;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * Do nothing for the moment
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class PushProductProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item): ProductInterface
    {
        return $item;
    }
}
