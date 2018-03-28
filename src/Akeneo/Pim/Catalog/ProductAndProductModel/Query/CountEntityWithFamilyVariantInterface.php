<?php

namespace Pim\Component\Catalog\ProductAndProductModel\Query;

use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Find the number of product and product models count belonging to the given family variant
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CountEntityWithFamilyVariantInterface
{
    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return int
     */
    public function belongingToFamilyVariant(FamilyVariantInterface $familyVariant): int;
}
