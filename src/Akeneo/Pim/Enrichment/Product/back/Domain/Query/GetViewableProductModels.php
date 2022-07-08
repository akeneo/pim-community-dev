<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetViewableProductModels
{
    /**
     * @param array<string> $productModelCodes
     * @param int $userId
     * @return array<string>
     */
    public function fromProductModelCodes(array $productModelCodes, int $userId): array;
}
