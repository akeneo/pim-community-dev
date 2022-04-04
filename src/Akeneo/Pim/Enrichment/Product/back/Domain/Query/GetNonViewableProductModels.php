<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetNonViewableProductModels
{
    /**
     * @return array<string>
     */
    public function fromProductModelIdentifiers(array $productModelIdentifiers, int $userId): array;
}
