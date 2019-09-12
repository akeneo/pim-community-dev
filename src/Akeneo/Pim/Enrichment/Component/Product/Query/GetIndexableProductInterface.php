<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\IndexableProduct;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetIndexableProductInterface
{
    /**
     * @param string $productIdentifier
     * @return IndexableProduct|null
     */
    public function fromProductIdentifier(string $productIdentifier): ?IndexableProduct;

    /**
     * @param array $productIdentifiers
     * @return array $productIdentifiers
     */
    public function fromProductIdentifiers(array $productIdentifiers): array;
}
