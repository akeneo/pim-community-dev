<?php

namespace Akeneo\Pim\Structure\Component\Repository;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;

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
     */
    public function getIdentifier(): AttributeInterface;

    /**
     * Get the main identifier attribute
     * Only one main identifier attribute can exist
     */
    public function getMainIdentifier(): AttributeInterface;

    /**
     * Get the identifier code
     */
    public function getIdentifierCode(): string;

    /**
     * Get the main identifier code
     */
    public function getMainIdentifierCode(): string;

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
     * Get attributes by group codes
     *
     * @param string[] $groupCodes
     * @param int $limit
     * @param string|null $searchAfter
     *
     * @return AttributeInterface[]
     */
    public function getAttributesByGroups(array $groupCodes, int $limit, ?string $searchAfter);

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
