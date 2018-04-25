<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Factory\ProductUniqueDataFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface;

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

    /** @var ProductUniqueDataRepositoryInterface */
    protected $repository;

    /** @var string */
    protected $uniqueDataClass;

    /**
     * @param ProductUniqueDataFactory             $factory
     * @param ProductUniqueDataRepositoryInterface $repository
     * @param string                               $uniqueDataClass
     */
    public function __construct(
        ProductUniqueDataFactory $factory,
        ProductUniqueDataRepositoryInterface $repository,
        string $uniqueDataClass
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->uniqueDataClass = $uniqueDataClass;
    }

    /**
     * @param ProductInterface $product
     */
    public function synchronize(ProductInterface $product)
    {
        $this->repository->deleteUniqueDataForProduct($product, $this->uniqueDataClass);
        $actualUniqueDataCollection = $this->createUniqueDataFromProduct($product);
        $product->setUniqueData(new ArrayCollection($actualUniqueDataCollection));
    }

    /**
     * Map values to Unique data for the given product
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    private function createUniqueDataFromProduct(ProductInterface $product): array {
        return array_map(
            function (ValueInterface $value) use ($product) {
                return $this->factory->create($product, $value);
            },
            $product->getValues()->getUniqueValues()
        );
    }
}
