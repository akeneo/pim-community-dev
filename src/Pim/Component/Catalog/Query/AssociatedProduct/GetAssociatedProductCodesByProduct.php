<?php

namespace Pim\Component\Catalog\Query\AssociatedProduct;

use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAssociatedProductCodesByProduct
{
    /**
     * Return codes of associated products
     *
     * @param int|string $productId
     * @param int $associationTypeId
     *
     * @return array
     */
    public function getCodes($productId, $associationTypeId);
}
