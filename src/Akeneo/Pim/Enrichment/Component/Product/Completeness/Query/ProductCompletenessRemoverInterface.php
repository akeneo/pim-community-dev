<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductCompletenessRemoverInterface
{
    /**
     * See deleteProducts() below
     */
    public function deleteForOneProduct(UuidInterface $productUuid): int;

    /**
     * Delete the elements from the completeness table
     * related to products passed as arguments
     * It returns the count of elements deleted.
     */
    public function deleteForProducts(array $productUuids): int;
}
