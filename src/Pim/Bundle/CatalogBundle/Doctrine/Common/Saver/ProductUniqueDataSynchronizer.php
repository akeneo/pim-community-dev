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
        $uniqueDataCollection = $product->getUniqueData();

        foreach ($product->getValues()->getUniqueValues() as $value) {
            $attribute = $value->getAttribute();

            $uniqueData = $this->getUniqueDataFromCollection($uniqueDataCollection, $attribute);
            if (null !== $uniqueData) {
                $uniqueData->setProductValue($value);
            } else {
                $uniqueData = $this->factory->create($product, $value);
                $product->addUniqueData($uniqueData);
            }
        }
    }

    /**
     * @param Collection         $uniqueDataCollection
     * @param AttributeInterface $attribute
     *
     * @return ProductUniqueDataInterface|null
     */
    protected function getUniqueDataFromCollection(Collection $uniqueDataCollection, AttributeInterface $attribute)
    {
        foreach ($uniqueDataCollection as $uniqueData) {
            if ($attribute === $uniqueData->getAttribute()) {
                return $uniqueData;
            }
        }

        return null;
    }
}
