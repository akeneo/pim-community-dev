<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoryCodes
{
    /**
     * @param ProductIdentifier[] $productIdentifiers
     * @return array<string, string[]> example:
     *  {
     *      "product1": ["categoryA", "categoryB"],
     *      "product2": ["categoryA"],
     *      ...
     *  }
     * @deprecated
     */
    public function fromProductIdentifiers(array $productIdentifiers): array;

    /**
     * @param UuidInterface[] $productUuids
     * @return array<string, string[]> example:
     *  {
     *      "uuid1": ["categoryA", "categoryB"],
     *      "uuid2": ["categoryA"],
     *      ...
     *  }
     */
    public function fromProductUuids(array $productUuids): array;
}
