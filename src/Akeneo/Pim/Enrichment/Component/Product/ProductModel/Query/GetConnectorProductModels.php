<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetConnectorProductModels
{
    public function fromProductModelCodes(
        array $codes,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): array;
}
