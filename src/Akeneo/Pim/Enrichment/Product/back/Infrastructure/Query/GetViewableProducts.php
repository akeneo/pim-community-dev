<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts as GetViewableProductsInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetViewableProducts implements GetViewableProductsInterface
{
    /**
     * {@inheritDoc}
     */
    public function fromProductIdentifiers(array $productIdentifiers, int $userId): array
    {
        return $productIdentifiers;
    }

    /**
     * {@inheritDoc}
     */
    public function fromProductUuids(array $productUuids, int $userId): array
    {
        return $productUuids;
    }
}
