<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Published product repository interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface PublishedProductRepositoryInterface extends ProductRepositoryInterface
{
    /**
     * Fetch a published product by the working copy product
     *
     * @param ProductInterface $originalProduct
     *
     * @return PublishedProductInterface
     */
    public function findOneByOriginalProduct(ProductInterface $originalProduct);

    /**
     * Fetch a published product by the working copy product id
     *
     * @param string|integer $originalProductId
     *
     * @return PublishedProductInterface
     */
    public function findOneByOriginalProductId($originalProductId);

    /**
     * Fetch a published product by the version
     *
     * @param string|integer $versionId
     *
     * @return PublishedProductInterface
     */
    public function findOneByVersionId($versionId);

    /**
     * Fetch many published products by a list of working copy product ids
     *
     * @param ProductInterface[] $originalProducts
     *
     * @return PublishedProductInterface[]
     */
    public function findByOriginalProducts(array $originalProducts);

    /**
     * Get the version that has been published for a given original product ID.
     * If none version has been published, null is returned.
     *
     * @param int|string $originalId
     *
     * @return int|null
     */
    public function getPublishedVersionIdByOriginalProductId($originalId);

    /**
     * Get the ID's of all published products.
     * The keys of the array are the ID of the original product.
     *
     * @param integer[] $originalIds
     *
     * @return array [ original product ID => published product ID ]
     */
    public function getProductIdsMapping(array $originalIds = []);

    /**
     * Count published products for a specific family
     *
     * @param FamilyInterface $family
     *
     * @return int
     */
    public function countPublishedProductsForFamily(FamilyInterface $family);

    /**
     * Count published products for a specific category
     *
     * @param CategoryInterface $category
     *
     * @return int
     */
    public function countPublishedProductsForCategory(CategoryInterface $category);

    /**
     * Count published products for a specific attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return int
     */
    public function countPublishedProductsForAttribute(AttributeInterface $attribute);

    /**
     * Count published products for a specific group
     *
     * @param GroupInterface $group
     *
     * @return int
     */
    public function countPublishedProductsForGroup(GroupInterface $group);

    /**
     * Count published products for a specific association type
     *
     * @param AssociationTypeInterface $associationType
     *
     * @return int
     */
    public function countPublishedProductsForAssociationType(AssociationTypeInterface $associationType);

    /**
     * Count published products for a specific attribute option
     *
     * @param AttributeOptionInterface $option
     *
     * @return int
     */
    public function countPublishedProductsForAttributeOption(AttributeOptionInterface $option);
}
