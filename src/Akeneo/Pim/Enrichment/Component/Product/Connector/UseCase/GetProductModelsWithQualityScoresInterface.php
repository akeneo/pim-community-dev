<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductModelsWithQualityScoresInterface
{
    public function fromConnectorProductModel(ConnectorProductModel $productModel): ConnectorProductModel;

    public function fromConnectorProductModelList(ConnectorProductModelList $connectorProductModelList, ?string $channel = null, array $locales = []): ConnectorProductModelList;
}
