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

        $filteredEntityWithFamilyVariant = clone $entityWithFamilyVariant;

        $this->setFilteredParent($filteredEntityWithFamilyVariant);

        return $filteredEntityWithFamilyVariant;
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

        // force to load product to avoid cloning of the proxy object, which does not copy value collection
        // but only properties declared in doctrine
        // @see Doctrine\ORM\Proxy\ProxyFactory::createCloner
        $parent->getCode();

        $parent = $this->filteredProductModelFactory->create($parent);
        $entityWithFamilyVariant->setParent($parent);

        return $entityWithFamilyVariant;
    }
}
