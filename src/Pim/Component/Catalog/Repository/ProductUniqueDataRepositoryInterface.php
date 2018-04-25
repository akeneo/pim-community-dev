<?php

namespace Pim\Component\Catalog\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Product unique data repository. Please see {@see Pim\Component\Catalog\Model\ProductUniqueDataInterface}
 * for more information.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductUniqueDataRepositoryInterface extends ObjectRepository
{
    /**
     * Returns true if a unique ProductValue with the provided data already exists in another product,
     * false otherwise.
     *
     * @param ValueInterface   $value
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function uniqueDataExistsInAnotherProduct(ValueInterface $value, ProductInterface $product);

    /**
     * Delete all unique data related to product
     *
     * @param ProductInterface $product
     * @param string           $uniqueDataClass
     */
    public function deleteUniqueDataForProduct(ProductInterface $product, string $uniqueDataClass);
}
