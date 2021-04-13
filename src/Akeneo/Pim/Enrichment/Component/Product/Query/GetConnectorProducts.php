<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetConnectorProducts
{
    /**
     * Ideally, we should not pass the PQB but a Product Query agnostic of the storage.
     * It would be much easier fake it.
     * @param ProductQueryBuilderInterface $productQueryBuilder
     * @param int $userId
     * @param array|null $attributesToFilterOn
     * @param string|null $channelToFilterOn
     * @param array|null $localesToFilterOn
     * @return ConnectorProductList
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $productQueryBuilder,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList;

    /**
     * @param string $productIdentifier
     * @param int $userId
     * @return ConnectorProduct
     * @throws ObjectNotFoundException when the product does not exist
     */
    public function fromProductIdentifier(string $productIdentifier, int $userId): ConnectorProduct;

    /**
    * @param string[] $productIdentifiers
    * @param int $userId
    * @param array|null $attributesToFilterOn
    * @param string|null $channelToFilterOn
    * @param array|null $localesToFilterOn
    * @return ConnectorProductList
    */
    public function fromProductIdentifiers(
        array $productIdentifiers,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList;
}
