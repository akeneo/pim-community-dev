<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ProductUniqueDataFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Synchronize the $uniqueData persistent collection of the product with the unique values of the product.
 * Those unique values come from the $values collection
 * {@see Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection}.
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

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(ProductUniqueDataFactory $factory, IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->factory = $factory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param ProductInterface $product
     */
    public function synchronize(ProductInterface $product)
    {
        // We get the unique data collection that we can to update
        $uniqueDataCollectionToUpdate = $product->getUniqueData();
        // We extract the attribute code list from this collection
        $attributeCodesToUpdate = $this->getAttributeCodes(
            $uniqueDataCollectionToUpdate->toArray()
        );

        // We get the actual unique data from the product that just got updated
        $actualUniqueDataCollection = $this->createUniqueDataFromProduct($product);
        // We also extract the attribute code list from this collection
        $actualAttributeCodes = $this->getAttributeCodes($actualUniqueDataCollection);

        // We substract from the original collection the new attributes to have the attribute codes to remove
        $attributeCodeToRemoveFromUniqueCollection = array_diff(
            $attributeCodesToUpdate,
            $actualAttributeCodes
        );
        $this->handleRemovals(
            $uniqueDataCollectionToUpdate,
            $attributeCodeToRemoveFromUniqueCollection
        );

        // We do the opposite to have the attribute codes to add
        $attributeCodeToAddToUniqueCollection = array_diff(
            $actualAttributeCodes,
            $attributeCodesToUpdate
        );
        $this->handleAdditions(
            $actualUniqueDataCollection,
            $uniqueDataCollectionToUpdate,
            $attributeCodeToAddToUniqueCollection
        );

        // We do union of the two arrays to get the attribute codes to update
        $attributeCodeToUpdateInUniqueCollection = array_intersect(
            $actualAttributeCodes,
            $attributeCodesToUpdate
        );
        $this->handleUpdates(
            $uniqueDataCollectionToUpdate,
            $attributeCodeToUpdateInUniqueCollection,
            $product
        );
    }

    private function handleRemovals(Collection $uniqueDataCollectionToUpdate, array $attributeCodes)
    {
        // We now map the corresponding UniqueDataInterface collection from the given attribute codes
        $uniqueDataCollectionToRemove = $this->getUniqueDataCollectionFromAttributeCodes(
            $uniqueDataCollectionToUpdate->toArray(),
            $attributeCodes
        );

        // We now iterate over the collection to remove UniqueData from the original collection
        foreach ($uniqueDataCollectionToRemove as $uniqueData) {
            $uniqueDataCollectionToUpdate->removeElement($uniqueData);
        }
    }

    private function handleAdditions(
        array $actualUniqueDataCollection,
        Collection $uniqueDataCollectionToUpdate,
        array $attributeCodes
    ) {
        // We now map the corresponding UniqueDataInterface collection from the given attribute codes
        $uniqueDataCollectionToAdd = $this->getUniqueDataCollectionFromAttributeCodes(
            $actualUniqueDataCollection,
            $attributeCodes
        );

        // We now iterate over the collection to add UniqueData to the original collection
        foreach ($uniqueDataCollectionToAdd as $uniqueData) {
            $uniqueDataCollectionToUpdate->add($uniqueData);
        }
    }

    private function handleUpdates(
        Collection $uniqueDataCollectionToUpdate,
        array $attributeCodes,
        ProductInterface $product
    ) {
        // We now map the corresponding UniqueDataInterface collection from the given attribute codes
        $uniqueDataCollectionToUpdateValue = $this->getUniqueDataCollectionFromAttributeCodes(
            $uniqueDataCollectionToUpdate->toArray(),
            $attributeCodes
        );

        // We now iterate over the collection to update the UniqueData of the original collection
        foreach ($uniqueDataCollectionToUpdateValue as $uniqueData) {
            $uniqueData->setAttribute($uniqueData->getAttribute());
            $value = $product->getValue($uniqueData->getAttribute()->getCode());
            $uniqueData->setRawData($value->__toString());
        }
    }

    private function getAttributeCodes(array $uniqueDataCollectionToUpdate)
    {
        return array_values(array_map(
            function ($uniqueData) {
                return $uniqueData->getAttribute()->getCode();
            },
            $uniqueDataCollectionToUpdate
        ));
    }

    private function getUniqueDataCollectionFromAttributeCodes(array $uniqueDataCollection, $attributeCodes)
    {
        return array_filter(
            $uniqueDataCollection,
            function (ProductUniqueDataInterface $uniqueData) use ($attributeCodes) {
                return in_array($uniqueData->getAttribute()->getCode(), $attributeCodes);
            }
        );
    }

    private function createUniqueDataFromProduct(ProductInterface $product)
    {
        $uniqueData = [];

        foreach ($product->getValues() as $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
            if (null !== $attribute && $attribute->isUnique() && $value->hasData()) {
                $uniqueData[] = $this->factory->create($product, $attribute, $value->__toString());
            }
        }

        return $uniqueData;
    }
}
