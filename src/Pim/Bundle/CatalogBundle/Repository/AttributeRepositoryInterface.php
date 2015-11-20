<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface;

/**
 * Repository interface for attribute
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRepositoryInterface extends
    ChoicesProviderInterface,
    IdentifiableObjectRepositoryInterface,
    ObjectRepository
{
    /**
     * @deprecated avoid the hydration of attributes as objects (performance), will be removed in 1.5
     *
     * @return AttributeInterface[]
     */
    public function findAllWithTranslations();

    /**
     * Find attributes with related attribute groups
     *
     * @param array $attributeIds
     * @param array $criterias
     *
     * @return array
     */
    public function findWithGroups(array $attributeIds = array(), array $criterias = array());

    /**
     * Find all attributes that belongs to the default group
     *
     * @deprecated avoid the hydration of attributes as objects (performance), will be removed in 1.5
     *
     * @return AttributeInterface[]
     */
    public function findAllInDefaultGroup();

    /**
     * Find all unique attribute codes
     *
     * @return string[]
     */
    public function findUniqueAttributeCodes();

    /**
     * Find media attribute codes
     *
     * @return string[]
     */
    public function findMediaAttributeCodes();

    /**
     * Find all attributes of type axis
     * An axis define a variation of a variant group
     * Axes are attributes with simple select option, not localizable and not scopable
     *
     * @return mixed a query builder
     */
    public function findAllAxisQB();

    /**
     * Find all axis
     *
     * @see findAllAxisQB
     *
     * @deprecated avoid the hydration of attributes as objects (performance), will be removed in 1.5
     *
     * @return array
     */
    public function findAllAxis();

    /**
     * Get available attributes as label as a choice
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return array
     */
    public function getAvailableAttributesAsLabelChoice();

    /**
     * Get attribute as array indexed by code
     *
     * @param bool   $withLabel translated label should be joined
     * @param string $locale    the locale code of the label
     * @param array  $ids       the attribute ids
     *
     * @return array
     */
    public function getAttributesAsArray($withLabel = false, $locale = null, array $ids = []);

    /**
     * Get ids of attributes usable in grid
     *
     * @param array $codes
     * @param array $groupIds
     *
     * @return array
     */
    public function getAttributeIdsUseableInGrid($codes = null, $groupIds = null);

    /**
     * Get ids from codes
     *
     * @param mixed $codes the attribute codes
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return array
     */
    public function getAttributeIds($codes);

    /**
     * @return mixed a query builder
     */
    public function createDatagridQueryBuilder();

    /**
     * Get the identifier attribute
     * Only one identifier attribute can exists
     *
     * @return AttributeInterface
     */
    public function getIdentifier();

    /**
     * Get the identifier code
     *
     * @return string
     */
    public function getIdentifierCode();

    /**
     * Get non identifier attributes
     *
     * @return AttributeInterface[]
     */
    public function getNonIdentifierAttributes();

    /**
     * Get attribute type by code attributes
     *
     * @param array $codes
     *
     * @return array
     */
    public function getAttributeTypeByCodes(array $codes);

    /**
     * Get attribute codes by attribute type
     *
     * @param string $type
     *
     * @return string[]
     */
    public function getAttributeCodesByType($type);

    /**
     * Return the number of existing attributes
     *
     * @return int
     */
    public function countAll();
}
