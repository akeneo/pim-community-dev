<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;

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
     * Return the number of groups containing the provided attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return int
     */
    public function countVariantGroupAxis(AttributeInterface $attribute);

    /**
     * @return mixed
     */
    public function createAssociationDatagridQueryBuilder();

    /**
     * @param string $dataLocale
     * @param int    $collectionId
     * @param string $search
     * @param array  $options
     *
     * @return array
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = []);

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
     * Get the variant group where its ProductTemplate is $productTemplate
     *
     * @param ProductTemplateInterface $productTemplate
     *
     * @return GroupInterface|null
     */
    public function getVariantGroupByProductTemplate(ProductTemplateInterface $productTemplate);

    /**
     * Check if a group has the attribute with specified code
     *
     * @param int[]  $id
     * @param string $attributeCode
     *
     * @return bool
     */
    public function hasAttribute(array $id, $attributeCode);
}
