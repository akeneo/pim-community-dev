<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetNonViewableCategoryCodes
{
    /**
     * @param ProductIdentifier[] $productIdentifiers
     * @return array<string, string[]> example:
     *  {
     *      "product1": ["categoryA", "categoryB"],
     *      "product2": ["categoryA"],
     *      ...
     *  }
     */
    public function fromProductIdentifiers(array $productIdentifiers, int $userId): array;
}
