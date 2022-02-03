<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;

interface IsUserCategoryGranted
{
    public function forProductAndAccessLevel(
        int $userId,
        ProductIdentifier $productIdentifier,
        string $accessLevel
    ): bool;
}
