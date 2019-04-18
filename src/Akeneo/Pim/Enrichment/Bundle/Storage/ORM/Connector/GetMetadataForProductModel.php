<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetMetadataInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetMetadataForProductModel implements GetMetadataInterface
{
    public function forProductModel(ProductModelInterface $productModel): array
    {
        return [];
    }
}
