<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Factory\ProductUniqueDataFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductUniqueDataInterface;

/**
 * Synchronize the $uniqueData persistent collection of the product with the unique values of the product.
 * Those unique values come from the $values collection
 * {@see Pim\Component\Catalog\Model\ValueCollectionInterface}.
 *
 * The only aim of the $uniqueData collection is to be able to save these information in the database via Doctrine.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUniqueDataSynchronizer
{
    /** @var ProductUniqueDataFactory */
    protected $factory;

    /**
     * @param ProductUniqueDataFactory $factory
     */
    public function __construct(ProductUniqueDataFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param ProductInterface $product
     */
    public function synchronize(ProductInterface $product)
    {
        $oldProductUniqueData = $product->getUniqueData();
        foreach ($oldProductUniqueData as $uniqueData) {
            $oldProductUniqueData->remove($uniqueData);
        }

        $newProductUniqueData = $product->getValues()->getUniqueValues();
        foreach ($newProductUniqueData as $value) {
            $uniqueData = $this->factory->create($product, $value);
            $product->addUniqueData($uniqueData);
        }
    }
}
