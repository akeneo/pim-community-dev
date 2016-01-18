<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;

/**
 * Group repository interface
 *
 * @author    Nicolas Dupont <janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Get ordered groups associative array id to label
     *
     * @param GroupTypeInterface $type
     *
     * @return array
     */
    public function getChoicesByType(GroupTypeInterface $type);

    /**
     * Get groups
     *
     * @return array
     */
    public function getChoices();

    /**
     * Return the number of groups containing the provided attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return int
     */
    public function countVariantGroupAxis(AttributeInterface $attribute);

    /**
     * Return the number of variant groups
     *
     * @return int
     */
    public function countVariantGroups();

    /**
     * @return mixed
     */
    public function createDatagridQueryBuilder();

    /**
     * @return mixed
     */
    public function createAssociationDatagridQueryBuilder();

    /**
     * {@inheritdoc}
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = array());

    /**
     * Get all non variant groups
     *
     * @return array
     */
    public function getAllGroupsExceptVariant();

    /**
     * Get all variant groups
     *
     * @return array
     */
    public function getAllVariantGroups();

    /**
     * Get all variant groups with ids $whereIn $variantGroupIds
     * If $whereIn is set to false, it makes a NOT IN request.
     *
     * @param array $variantGroupIds
     * @param bool  $whereIn
     *
     * @return array
     */
    public function getVariantGroupsByIds(array $variantGroupIds, $whereIn = true);

    /**
     * Get all variant group ids
     *
     * @return array
     */
    public function getAllVariantGroupIds();

    /**
     * Get variant groups where all their attributes are in $attributeIds
     *
     * @param array $attributeIds
     *
     * @return array
     */
    public function getVariantGroupsByAttributeIds(array $attributeIds);

    /**
     * Get the variant group where its ProductTemplate is $productTemplate
     *
     * @param ProductTemplateInterface $productTemplate
     *
     * @return GroupInterface|null
     */
    public function getVariantGroupByProductTemplate(ProductTemplateInterface $productTemplate);
}
