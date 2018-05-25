<?php
declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Component\Catalog\Model\ProductInterface;

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
