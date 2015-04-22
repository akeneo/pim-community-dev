<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;

/**
 * Product id filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdFilter extends AbstractFilter implements FieldFilterInterface
{
    /** @var array */
    protected $supportedFields;

    /**
     * Instanciate the filter
     *
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
        if (!is_string($value) && !is_array($value)) {
            throw InvalidArgumentException::expected($field, 'array or string value', 'filter', 'productId', $value);
        }

        $field = '_id';
        $value = is_array($value) ? $value : [$value];

        $this->applyFilter($value, $field, $operator);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param array  $value
     * @param string $field
     * @param string $operator
     */
    protected function applyFilter(array $value, $field, $operator)
    {
        if ($operator === Operators::NOT_IN_LIST) {
            $this->qb->field($field)->notIn($value);
        } else {
            $this->qb->field($field)->in($value);
        }
    }
}
