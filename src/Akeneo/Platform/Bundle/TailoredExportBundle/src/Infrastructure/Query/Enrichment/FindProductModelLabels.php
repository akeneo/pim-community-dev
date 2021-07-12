<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindProductModelLabelsInterface;

class FindProductModelLabels implements FindProductModelLabelsInterface
{
    private GetProductModelLabelsInterface $getProductModelLabels;

    public function __construct(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $this->getProductModelLabels = $getProductModelLabels;
    }

    public function byCodes(array $productModelCode, string $channel, string $locale): array
    {
        return $this->getProductModelLabels->byCodesAndLocaleAndScope($productModelCode, $locale, $channel);
    }
}
