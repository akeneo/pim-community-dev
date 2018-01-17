<?php

declare(strict_types=1);

namespace PimEnterprise\Component\Catalog\Security\Filter;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use PimEnterprise\Component\Catalog\Security\Factory\FilteredEntityFactory;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;

/**
 * Filter not granted values, associations, categories and parents in parents of entities with family variants
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class NotGrantedParentFilter implements NotGrantedDataFilterInterface
{
    /** @var FilteredEntityFactory */
    private $filteredProductModelFactory;

    /**
     * @param FilteredEntityFactory $filteredProductModelFactory
     */
    public function __construct(FilteredEntityFactory $filteredProductModelFactory)
    {
        $this->filteredProductModelFactory = $filteredProductModelFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($entityWithFamilyVariant)
    {
        if (!$entityWithFamilyVariant instanceof EntityWithFamilyVariantInterface) {
            return $entityWithFamilyVariant;
        }
        $this->setFilteredParent($entityWithFamilyVariant);

        return $entityWithFamilyVariant;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return EntityWithFamilyVariantInterface
     */
    private function setFilteredParent(
        EntityWithFamilyVariantInterface $entityWithFamilyVariant
    ): EntityWithFamilyVariantInterface {
        if (null === $parent = $entityWithFamilyVariant->getParent()) {
            return $entityWithFamilyVariant;
        }

        $parent = $this->filteredProductModelFactory->create($parent);
        $entityWithFamilyVariant->setParent($parent);

        return $entityWithFamilyVariant;
    }
}
