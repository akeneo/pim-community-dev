<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Groups filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsFilter extends AbstractFilter implements FieldFilterInterface
{
    /** @var array */
    protected $supportedFields;

    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $value);
        }

        if (FieldFilterHelper::ID_PROPERTY === FieldFilterHelper::getProperty($field)) {
            if (null !== $value) {
                $value = array_map('intval', $value);
            }
            $this->applyIdFilter('groupIds', $operator, $value);
        } else {
            $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
            $this->applyCodeFilter($field, $operator, $value);
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'groups');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'groups');
        }
    }

    /**
     * Apply the filter to the query with the given operator for group ids
     *
     * @param string     $field
     * @param string     $operator
     * @param array|null $value
     */
    protected function applyIdFilter($field, $operator, $value)
    {
        switch ($operator) {
            case Operators::IN_LIST:
                $this->qb->field($field)->in($value);
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->field($field)->notIn($value);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->size(0);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($field)->where(sprintf('%s.length > 0', $field));
                break;
        }
    }

    /**
     * Apply the filter to the query with the given operator for group codes
     *
     * @param string     $field
     * @param string     $operator
     * @param array|null $value
     */
    protected function applyCodeFilter($field, $operator, $value)
    {
        switch ($operator) {
            case Operators::IN_LIST:
                $this->qb->field($field)->in($value);
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->field($field)->notIn($value);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($field)->exists(true);
                break;
        }
    }
}
