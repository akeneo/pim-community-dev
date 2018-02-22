<?php

namespace Pim\Bundle\ConnectorBundle\Doctrine\Common\Detacher;

use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Detach at once products that have been stored for.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface StoredProductDetacherInterface
{
    /**
     * Stores the products to be bulk detached.
     *
     * @param ProductInterface $product
     */
    public function storeProductToDetach(ProductInterface $product);

    /**
     * Detaches the products that have been stored.
     */
    public function detachStoredProducts();
}
