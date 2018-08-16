<?php

namespace Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * Find the number of product count belonging to the given family
 *
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CountProductsWithFamilyInterface
{
    /**
     * @param FamilyInterface $family
     *
     * @return int
     */
    public function count(FamilyInterface $family): int;
}
