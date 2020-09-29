<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment;

interface GetDescendantVariantProductIdsQueryInterface
{
    public function fromProductModelIds(array $productModelIds): array;
}
