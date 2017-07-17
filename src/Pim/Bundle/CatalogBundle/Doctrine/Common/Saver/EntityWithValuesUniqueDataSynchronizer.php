<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Factory\EntityWithValuesUniqueDataFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\EntityWithValuesUniqueDataInterface;

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
class EntityWithValuesUniqueDataSynchronizer
{
    /** @var EntityWithValuesUniqueDataFactory */
    protected $factory;

    /**
     * @param EntityWithValuesUniqueDataFactory $factory
     */
    public function __construct(EntityWithValuesUniqueDataFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param EntityWithValuesInterface $entityWithValues
     */
    public function synchronize(EntityWithValuesInterface $entityWithValues)
    {
        $uniqueDataCollection = $entityWithValues->getUniqueData();

        foreach ($entityWithValues->getValues()->getUniqueValues() as $value) {
            $attribute = $value->getAttribute();

            $uniqueData = $this->getUniqueDataFromCollection($uniqueDataCollection, $attribute);
            if (null !== $uniqueData) {
                $uniqueData->setValue($value);
            } else {
                $uniqueData = $this->factory->create($entityWithValues, $value);
                $entityWithValues->addUniqueData($uniqueData);
            }
        }
    }

    /**
     * @param Collection         $uniqueDataCollection
     * @param AttributeInterface $attribute
     *
     * @return EntityWithValuesUniqueDataInterface|null
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
