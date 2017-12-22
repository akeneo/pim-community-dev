<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Repository;

use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;

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
     * @param string|int $originalProductId
     *
     * @return PublishedProductInterface
     */
    public function findOneByOriginalProductId($originalProductId);

    /**
     * Fetch a published product by the version
     *
     * @param string|int $versionId
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
     * @param int[] $originalIds
     *
     * @return array [ original product ID => published product ID ]
     */
    public function getProductIdsMapping(array $originalIds = []);

    /**
     * Count published products for a specific association type
     *
     * @param AssociationTypeInterface $associationType
     *
     * @return int
     */
    public function countPublishedProductsForAssociationType(AssociationTypeInterface $associationType);
}
