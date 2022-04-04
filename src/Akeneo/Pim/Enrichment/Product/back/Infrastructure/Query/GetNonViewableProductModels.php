<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use \Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableProductModels as GetNonViewableProductModelsInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetNonViewableProductModels implements GetNonViewableProductModelsInterface
{
    /**
     * @inerhitDoc
     */
    public function fromProductModelIdentifiers(array $productModelIdentifiers, int $userId): array
    {
        return [];
    }
}
