<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;

interface GetDescendantVariantProductIdsQueryInterface
{
    public function fromProductModelIds(ProductIdCollection $productModelIdCollection): array;
}
