<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;

interface GetProductsWithQualityScoresInterface
{
    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct;

    public function fromConnectorProductList(ConnectorProductList $connectorProductList): ConnectorProductList;
}
