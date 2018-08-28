<?php

namespace Pim\Component\Catalog\Query\AssociatedProduct;

use Pim\Component\Catalog\Model\AssociationInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAssociatedProductCodesByProduct
{
    /**
     * Return codes of associated products
     *
     * @param AssociationInterface $association
     *
     * @return array
     */
    public function getCodes(int $productId, AssociationInterface $association);
}
