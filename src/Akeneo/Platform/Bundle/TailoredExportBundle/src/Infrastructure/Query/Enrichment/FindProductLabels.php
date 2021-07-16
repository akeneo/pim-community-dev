<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindProductLabelsInterface;

class FindProductLabels implements FindProductLabelsInterface
{
    private GetProductLabelsInterface $getProductLabels;

    public function __construct(GetProductLabelsInterface $getProductLabels)
    {
        $this->getProductLabels = $getProductLabels;
    }

    /**
     * @inheritDoc
     */
    public function byIdentifiers(array $productIdentifiers, string $channel, string $locale): array
    {
        return $this->getProductLabels->byIdentifiersAndLocaleAndScope($productIdentifiers, $locale, $channel);
    }
}
