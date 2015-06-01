<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface as BaseProductRepositoryInterface;

/**
 * MongoDB product repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductRepositoryInterface extends BaseProductRepositoryInterface, AssociationRepositoryInterface
{
    /**
     * @param AttributeInterface $attribute
     *
     * @return string[]
     */
    public function findAllIdsForAttribute(AttributeInterface $attribute);

    /**
     * @param FamilyInterface $family
     *
     * @return string[]
     */
    public function findAllIdsForFamily(FamilyInterface $family);

    /**
     * @param CategoryInterface $category
     *
     * @return ProductInterface[]
     */
    public function findAllForCategory(CategoryInterface $category);

    /**
     * @param GroupInterface $group
     *
     * @return ProductInterface[]
     */
    public function findAllForGroup(GroupInterface $group);

    /**
     * @param int $id
     */
    public function cascadeFamilyRemoval($id);

    /**
     * @param int $id
     */
    public function cascadeAttributeRemoval($id);

    /**
     * @param int $id
     */
    public function cascadeCategoryRemoval($id);

    /**
     * @param int $id
     */
    public function cascadeGroupRemoval($id);

    /**
     * @param int $id
     */
    public function cascadeAssociationTypeRemoval($id);

    /**
     * @param int $id
     */
    public function cascadeAttributeOptionRemoval($id);

    /**
     * @param int $id
     */
    public function cascadeChannelRemoval($id);

    /**
     * @param int $productId
     * @param int $assocTypeCount
     */
    public function removeAssociatedProduct($productId, $assocTypeCount);
}
