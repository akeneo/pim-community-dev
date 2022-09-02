<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindProductModelLabelsInterface;

class FindProductModelLabels implements FindProductModelLabelsInterface
{
    public function __construct(
        private GetProductModelLabelsInterface $getProductModelLabels,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function byCodes(array $productModelCodes, string $channel, string $locale): array
    {
        return $this->getProductModelLabels->byCodesAndLocaleAndScope($productModelCodes, $locale, $channel);
    }
}
