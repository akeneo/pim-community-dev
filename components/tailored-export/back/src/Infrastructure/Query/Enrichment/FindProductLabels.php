<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindProductLabelsInterface;

class FindProductLabels implements FindProductLabelsInterface
{
    public function __construct(
        private GetProductLabelsInterface $getProductLabels,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function byIdentifiers(array $productIdentifiers, string $channel, string $locale): array
    {
        return $this->getProductLabels->byIdentifiersAndLocaleAndScope($productIdentifiers, $locale, $channel);
    }
}
