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
    public function findAllInDefaultGroup(): array;

    /**
     * Find all unique attribute codes
     *
     * @return string[]
     */
    public function findUniqueAttributeCodes(): array;

    /**
     * Find media attribute codes
     *
     * @return string[]
     */
    public function findMediaAttributeCodes(): array;

    /**
     * Find all attributes of type axis
     * An axis define a variation of a variant group
     * Axes are attributes with simple select option, not localizable and not scopable
     */
    public function findAllAxesQB(): QueryBuilder;

    /**
     * Get attribute as array indexed by code
     *
     * @param bool   $withLabel translated label should be joined
     * @param string $locale    the locale code of the label
     * @param array  $ids       the attribute ids
     */
    public function getAttributesAsArray(bool $withLabel = false, string $locale = null, array $ids = []): array;

    /**
     * Get ids of attributes usable in grid
     *
     * TODO: should be extracted in an enrich bundle repository
     *
     * @param array $codes
     * @param array $groupIds
     */
    public function getAttributeIdsUseableInGrid(array $codes = null, array $groupIds = null): array;

    /**
     * Get the identifier attribute
     * Only one identifier attribute can exists
     */
    public function getIdentifier(): AttributeInterface;

    /**
     * Get the identifier code
     */
    public function getIdentifierCode(): string;

    /**
     * Get attribute type by code attributes
     *
     * @param array $codes
     */
    public function getAttributeTypeByCodes(array $codes): array;

    /**
     * Get attribute codes by attribute type
     *
     * @param string $type
     *
     * @return string[]
     */
    public function getAttributeCodesByType(string $type): array;

    /**
     * Get attribute codes by attribute group
     *
     * @param AttributeGroupInterface $group
     *
     * @return string[]
     */
    public function getAttributeCodesByGroup(AttributeGroupInterface $group): array;

    /**
     * Get attributes by family
     *
     * @param FamilyInterface $family
     *
     * @return AttributeInterface[]
     */
    public function findAttributesByFamily(FamilyInterface $family): array;


    /**
     * Find axis label for a locale
     *
     * @param string $locale
     */
    public function findAvailableAxes(string $locale): array;
}
