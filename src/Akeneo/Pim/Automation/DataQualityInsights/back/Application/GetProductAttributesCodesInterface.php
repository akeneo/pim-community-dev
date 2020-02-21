<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

interface GetProductAttributesCodesInterface
{
    public function getTextarea(ProductId $productId): array;

    public function getText(ProductId $productId): array;

    public function getLocalizableText(ProductId $productId): array;
}
