<?php

namespace Akeneo\Pim\Structure\Component\Repository;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Repository interface for attribute
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRepositoryInterface extends
    IdentifiableObjectRepositoryInterface,
    ObjectRepository
{
    /**
     * Find all attributes that belongs to the default group
     *
     * @deprecated avoid the hydration of attributes as objects (perf), use from controller, will be removed in 1.8
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
     * @return QueryBuilder
     */
    public function findAllAxesQB();

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
     * TODO: should be extracted in an enrich bundle repository
     *
     * @param array $codes
     * @param array $groupIds
     *
     * @return array
     */
    public function getAttributeIdsUseableInGrid($codes = null, $groupIds = null);

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
     * Get attribute codes by attribute group
     *
     * @param AttributeGroupInterface $group
     *
     * @return string[]
     */
    public function getAttributeCodesByGroup(AttributeGroupInterface $group);

    /**
     * Get attributes by family
     *
     * @param FamilyInterface $family
     *
     * @return AttributeInterface[]
     */
    public function findAttributesByFamily(FamilyInterface $family);


    /**
     * Find axis label for a locale
     *
     * @param string $locale
     *
     * @return array
     */
    public function findAvailableAxes($locale);
}
