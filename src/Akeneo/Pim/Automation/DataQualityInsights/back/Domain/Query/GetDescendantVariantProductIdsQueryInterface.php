<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;

interface GetDescendantVariantProductIdsQueryInterface
{
    public function fromProductModelIds(array $productModelIds): array;
}
