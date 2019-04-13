<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValuesNormalizer;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectorProductNormalizer
{
    /** @var ProductValuesNormalizer */
    private $valuesNormalizer;

    /** @var DateTimeNormalizer */
    private $dateTimeNormalizer;

    public function __construct(ProductValuesNormalizer $valuesNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    public function normalizeConnectorProductList(ConnectorProductList $connectorProducts): array
    {
        $normalizedProducts =[];
        foreach ($connectorProducts->connectorProducts() as $connectorProduct) {
            $normalizedProducts[] = $this->normalizeConnectorProduct($connectorProduct);
        }

        return $normalizedProducts;
    }

    public function normalizeConnectorProduct(ConnectorProduct $connectorProduct)
    {
        return  [
            'identifier' => $connectorProduct->identifier(),
            'created' => $this->dateTimeNormalizer->normalize($connectorProduct->createdDate()),
            'updated' => $this->dateTimeNormalizer->normalize($connectorProduct->updatedDate()),
            'enabled' => $connectorProduct->enabled(),
            'family' => $connectorProduct->familyCode(),
            'categories' => $connectorProduct->categoryCodes(),
            'groups' => $connectorProduct->groupCodes(),
            'parent' => $connectorProduct->parentProductModelCode(),
            'values' => $this->valuesNormalizer->normalize($connectorProduct->values()),
            'associations' => $connectorProduct->associations()
        ];
    }
}
