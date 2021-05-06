<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductsWithCompletenesses implements GetProductsWithCompletenessesInterface
{
    private GetProductCompletenesses $getProductCompletenesses;

    public function __construct(GetProductCompletenesses $getProductCompletenesses)
    {
        $this->getProductCompletenesses = $getProductCompletenesses;
    }

    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct
    {
        return $product->buildWithCompletenesses($this->getProductCompletenesses->fromProductId($product->id()));
    }

    public function fromConnectorProductList(
        ConnectorProductList $connectorProductList,
        ?string $channel = null,
        array $locales = []
    ): ConnectorProductList {
        $productIdentifiers = array_map(
            fn (ConnectorProduct $connectorProduct) => $connectorProduct->id(),
            $connectorProductList->connectorProducts()
        );

        $productCompletenesses = $this->getProductCompletenesses->fromProductIds(
            $productIdentifiers,
            $channel,
            $locales
        );

        return new ConnectorProductList(
            $connectorProductList->totalNumberOfProducts(),
            array_map(
                fn (ConnectorProduct $product) =>
                    $product->buildWithCompletenesses($productCompletenesses[$product->id()]),
                $connectorProductList->connectorProducts()
            )
        );
    }
}
