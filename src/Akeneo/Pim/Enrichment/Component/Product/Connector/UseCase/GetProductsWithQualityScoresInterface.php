<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;

interface GetProductsWithQualityScoresInterface
{
    // @fixme: Find a better place to hold this constant
    public const FLAT_FIELD_PREFIX = 'dqi_quality_score';

    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct;

    public function fromConnectorProductList(ConnectorProductList $connectorProductList, ?string $channel = null, array $locales = []): ConnectorProductList;

    public function fromNormalizedProduct(string $productIdentifier, array $normalizedProduct, ?string $channel = null, array $locales = []): array;
}
