<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;

/**
 * Completeness filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends AbstractFilter implements FieldFilterInterface
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
        $this->checkValue($field, $value, $locale, $scope);

        $field = sprintf(
            "%s.%s.%s-%s",
            ProductQueryUtility::NORMALIZED_FIELD,
            'completenesses',
            $scope,
            $locale
        );
        $value = intval($value);

        $this->applyFilter($value, $field, $operator);

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string      $field
     * @param mixed       $value
     * @param string|null $locale
     * @param string|null $scope
     */
    protected function checkValue($field, $value, $locale, $scope)
    {
        if (!is_numeric($value)) {
            throw InvalidArgumentException::numericExpected($field, 'filter', 'completeness', gettype($value));
        }

        if (null === $locale || null === $scope) {
            throw InvalidArgumentException::localeAndScopeExpected($field, 'filter', 'completeness');
        }
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param int    $value
     * @param string $field
     * @param string $operator
     */
    protected function applyFilter($value, $field, $operator)
    {
        switch ($operator) {
            case Operators::EQUALS:
                $this->qb->field($field)->equals($value);
                break;
            case Operators::LOWER_THAN:
                $this->qb->field($field)->lt($value);
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($field)->gt($value);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $this->qb->field($field)->lte($value);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $this->qb->field($field)->gte($value);
                break;
        }
    }
}
