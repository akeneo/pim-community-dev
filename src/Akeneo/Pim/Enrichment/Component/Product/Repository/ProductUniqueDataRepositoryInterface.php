<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Product unique data repository. Please see {@see Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface}
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
}
