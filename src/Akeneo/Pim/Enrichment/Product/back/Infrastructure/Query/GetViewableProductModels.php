<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use \Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels as GetViewableProductModelsInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetViewableProductModels implements GetViewableProductModelsInterface
{
    /**
     * @inerhitDoc
     */
    public function fromProductModelCodes(array $productModelCodes, int $userId): array
    {
        return $productModelCodes;
    }
}
