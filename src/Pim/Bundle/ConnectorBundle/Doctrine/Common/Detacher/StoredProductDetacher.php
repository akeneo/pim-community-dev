<?php

namespace Pim\Bundle\ConnectorBundle\Doctrine\Common\Detacher;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Detach at once products that have been stored for.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StoredProductDetacher implements StoredProductDetacherInterface
{
    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var ProductInterface[] */
    protected $productsToBeDetached = [];

    /**
     * @param ObjectDetacherInterface $objectDetacher
     */
    public function __construct(ObjectDetacherInterface $objectDetacher)
    {
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function storeProductToDetach(ProductInterface $product)
    {
        $this->productsToBeDetached[] = $product;
    }

    /**
     * {@inheritdoc}
     */
    public function detachStoredProducts()
    {
        foreach ($this->productsToBeDetached as $product) {
            foreach ($product->getAssociations() as $association) {
                foreach ($association->getProducts() as $associatedProduct) {
                    $this->detachProductWithGroups($associatedProduct);
                }
            }

            $this->detachProductWithGroups($product);
        }

        $this->productsToBeDetached = [];
    }

    /**
     * @param ProductInterface $product
     */
    protected function detachProductWithGroups(ProductInterface $product)
    {
        foreach ($product->getGroups() as $group) {
            $this->objectDetacher->detach($group);
        }

        $this->objectDetacher->detach($product);
    }
}
