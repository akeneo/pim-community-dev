<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Pim\Bundle\FlexibleEntityBundle\Exception\UnknownAttributeException;

/**
 * Repository interface for flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FlexibleEntityRepositoryInterface extends TranslatableInterface, ScopableInterface
{
    /**
     * Get flexible entity config
     *
     * @return array $config
     */
    public function getFlexibleConfig();

    /**
     * Set flexible entity config
     *
     * @param array $config
     *
     * @return FlexibleEntityRepositoryInterface
     */
    public function setFlexibleConfig($config);

    /**
     * Finds attributes
     *
     * @param array $attributeCodes attribute codes
     *
     * @throws UnknownAttributeException
     *
     * @return array The objects.
     */
    public function getCodeToAttributes(array $attributeCodes);

    /**
     * Finds entities and attributes values by a set of criteria, same coverage than findBy
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     */
    public function findByWithAttributesQB(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    );

    /**
     * Finds entities and attributes values by a set of criteria, same coverage than findBy
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     */
    public function findByWithAttributes(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    );

    /**
     * Apply a filter by attribute value
     *
     * @param QueryBuilder $qb             query builder to update
     * @param string       $attributeCode  attribute code
     * @param string|array $attributeValue value(s) used to filter
     * @param string       $operator       operator to use
     */
    public function applyFilterByAttribute(QueryBuilder $qb, $attributeCode, $attributeValue, $operator = '=');

    /**
     * Apply a sort by attribute value
     *
     * @param QueryBuilder $qb            query builder to update
     * @param string       $attributeCode attribute code
     * @param string       $direction     direction to use
     */
    public function applySorterByAttribute(QueryBuilder $qb, $attributeCode, $direction);

    /**
     * Find entity with attributes values
     *
     * @param int $id entity id
     *
     * @return Entity the entity
     */
    public function findWithAttributes($id);

    /**
     * Load a flexible entity with its attributes sorted by sortOrder
     *
     * @param integer $id
     *
     * @return AbstractFlexible|null
     * @throws NonUniqueResultException
     */
    public function findWithSortedAttribute($id);
}
