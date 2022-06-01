<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Uuid;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductsWithCompletenesses
{
    public function __construct(private GetProductCompletenesses $getProductCompletenesses)
    {
    }

    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct
    {
        return $product->buildWithCompletenesses($this->getProductCompletenesses->fromProductUuid(Uuid::fromString($product->uuid())));
    }

    public function fromConnectorProductList(
        ConnectorProductList $connectorProductList,
        ?string $channel = null,
        array $locales = []
    ): ConnectorProductList {
        $productUuids = array_map(
            fn (ConnectorProduct $connectorProduct): UuidInterface => Uuid::fromString($connectorProduct->uuid()),
            $connectorProductList->connectorProducts()
        );

        $productCompletenesses = $this->getProductCompletenesses->fromProductUuids(
            $productUuids,
            $channel,
            $locales
        );

        return new ConnectorProductList(
            $connectorProductList->totalNumberOfProducts(),
            array_map(
                fn (ConnectorProduct $product) =>
                    $product->buildWithCompletenesses($productCompletenesses[$product->uuid()]),
                $connectorProductList->connectorProducts()
            )
        );
    }
}
