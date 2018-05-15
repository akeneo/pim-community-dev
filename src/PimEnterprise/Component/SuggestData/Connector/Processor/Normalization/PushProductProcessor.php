<?php
declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;

/**
 * Normalize a product from object to PIM.ai format.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class PushProductProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item): array
    {
        $product = [
            'identifier' => $item->getIdentifier()
        ];

        return $product;
    }
}
