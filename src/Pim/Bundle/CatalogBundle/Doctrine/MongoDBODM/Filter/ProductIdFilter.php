<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Product id filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (!is_string($value) && !is_array($value)) {
            throw InvalidArgumentException::expected($field, 'array or string value', 'filter', 'productId', $value);
        }

        $this->applyFilter('_id', $operator, $value);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string       $field
     * @param string       $operator
     * @param string|array $value
     */
    protected function applyFilter($field, $operator, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
                $this->qb->field($field)->equals($value);
                break;
            case Operators::NOT_EQUAL:
                $this->qb->field($field)->notEqual($value);
                break;
            case Operators::IN_LIST:
                $this->qb->field($field)->in($value);
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->field($field)->notIn($value);
                break;
        }
    }
}
