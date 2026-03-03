<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

interface GetDescendantVariantProductUuidsQueryInterface
{
    /**
     * @return string[]
     */
    public function fromProductModelIds(ProductEntityIdCollection $productModelIdCollection): array;
}
