<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Group manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupManager
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var string */
    protected $groupClass;

    /** @var string */
    protected $groupTypeClass;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $attributeClass;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param RegistryInterface          $doctrine
     * @param ProductRepositoryInterface $productRepository
     * @param string                     $groupClass
     * @param string                     $groupTypeClass
     * @param string                     $productClass
     * @param string                     $attributeClass
     */
    public function __construct(
        RegistryInterface $doctrine,
        ProductRepositoryInterface $productRepository,
        $groupClass,
        $groupTypeClass,
        $productClass,
        $attributeClass
    ) {
        $this->doctrine          = $doctrine;
        $this->groupClass        = $groupClass;
        $this->groupTypeClass    = $groupTypeClass;
        $this->productClass      = $productClass;
        $this->attributeClass    = $attributeClass;
        $this->productRepository = $productRepository;
    }

    /**
     * Get available axis
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeInterface[]
     */
    public function getAvailableAxis()
    {
        return $this->getAttributeRepository()->findAllAxis();
    }

    /**
     * Get axis as choice list
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return array
     */
    public function getAvailableAxisChoices()
    {
        $attributes = $this->getAvailableAxis();

        $choices = [];
        foreach ($attributes as $attribute) {
            $choices[$attribute->getId()] = $attribute->getLabel();
        }
        asort($choices);

        return $choices;
    }

    /**
     * Get choices
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return array
     */
    public function getChoices()
    {
        $choices = $this->getRepository()->getChoices();
        asort($choices);

        return $choices;
    }

    /**
     * Get axis as choice list
     *
     * @param bool $isVariant
     *
     * @return array
     */
    public function getTypeChoices($isVariant)
    {
        $types = $this->getGroupTypeRepository()->findBy(['variant' => $isVariant]);

        $choices = [];
        foreach ($types as $type) {
            $choices[$type->getId()] = $type->getLabel();
        }
        asort($choices);

        return $choices;
    }

    /**
     * Returns the entity repository
     *
     * @deprecated not used anymore from the outside of this class, will be passed to protected in 1.5
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->doctrine->getRepository($this->groupClass);
    }

    /**
     * Returns the group type repository
     *
     * @deprecated not used anymore from the outside of this class, will be passed to protected in 1.5
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getGroupTypeRepository()
    {
        return $this->doctrine->getRepository($this->groupTypeClass);
    }

    /**
     * Returns an array containing a limited number of product groups, and the total number of products
     *
     * @param GroupInterface $group
     * @param int            $maxResults
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return array
     */
    public function getProductList(GroupInterface $group, $maxResults)
    {
        $products = $this->productRepository->getProductsByGroup($group, $maxResults);
        $count = $this->productRepository->getProductCountByGroup($group);

        return ['products' => $products, 'productCount' => $count];
    }

    /**
     * Get the attribute repository
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return AttributeRepositoryInterface
     */
    protected function getAttributeRepository()
    {
        return $this->doctrine->getRepository($this->attributeClass);
    }
}
