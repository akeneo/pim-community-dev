<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetConnectorProductModels
{
    /**
     * Ideally, we should not pass the PQB but a Product Query agnostic of the storage.
     * It would be much easier to fake it.
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $productQueryBuilder,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductModelList;

    /**
     * @throws ObjectNotFoundException if the product model was not found
     */
    public function fromProductModelCode(string $productModelCode, int $userId): ConnectorProductModel;
}
