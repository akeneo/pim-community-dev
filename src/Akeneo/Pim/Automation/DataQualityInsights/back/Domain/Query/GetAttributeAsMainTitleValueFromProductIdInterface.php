<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

interface GetAttributeAsMainTitleValueFromProductIdInterface
{
    public function execute(ProductId $productId): array;
}
