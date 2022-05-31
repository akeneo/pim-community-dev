<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Uuid;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetConnectorProducts
{
    /**
     * @param UuidInterface $productUuid
     * @param int $userId
     * @return ConnectorProduct
     * @throws ObjectNotFoundException when the product does not exist
     */
    public function fromProductUuid(UuidInterface $productUuid, int $userId): ConnectorProduct;

    /**
    * @param UuidInterface[] $productUuids
    * @param int $userId
    * @param array|null $attributesToFilterOn
    * @param string|null $channelToFilterOn
    * @param array|null $localesToFilterOn
    * @return ConnectorProductList
    */
    public function fromProductUuids(
        array $productUuids,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList;
}
