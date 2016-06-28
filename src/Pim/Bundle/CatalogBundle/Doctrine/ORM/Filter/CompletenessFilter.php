<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\CompletenessJoin;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Completeness filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends AbstractFieldFilter implements FieldFilterInterface
{
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
     *
     * If locale is omitted, all products having a matching completeness for
     * the specified scope (no matter on which locale) will be selected.
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        switch ($operator) {
            case Operators::COMPLETE_ON_ALL_LOCALES:
                $this->checkOptions($field, $options);

                foreach ($options['locales'] as $locale) {
                    $this->addFilter(Operators::EQUALS, '100', $locale, $scope);
                }
                break;

            case Operators::NOT_COMPLETE_ON_ALL_LOCALES:
                $this->checkOptions($field, $options);

                foreach ($options['locales'] as $locale) {
                    $this->addFilter(Operators::LOWER_THAN, '100', $locale, $scope);
                }
                break;

            default:
                $this->checkValue($field, $value, $locale, $scope);
                $this->addFilter($operator, $value, $locale, $scope);
                break;
        }

        return $this;
    }

    /**
     * @param string $operator
     * @param string $value
     * @param null   $locale
     * @param null   $scope
     */
    protected function addFilter($operator, $value, $locale = null, $scope = null)
    {
        $joinAlias = $this->getUniqueAlias('filterCompleteness');
        $field     = $joinAlias . '.ratio';
        $util      = new CompletenessJoin($this->qb);
        $util->addJoins($joinAlias, $locale, $scope);

        $this->qb->andWhere($this->prepareCriteriaCondition($field, $operator, $value));
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

        if (null === $scope) {
            throw InvalidArgumentException::scopeExpected($field, 'filter', 'completeness');
        }
    }

    /**
     * Check if options are valid for COMPLETE_ON_ALL_LOCALES and NOT_COMPLETE_ON_ALL_LOCALES operators
     *
     * @param string $field
     * @param array  $options
     */
    protected function checkOptions($field, array $options)
    {
        if (!array_key_exists('locales', $options)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $field,
                'locales',
                'filter',
                'completeness',
                print_r($options, true)
            );
        }

        if (!isset($options['locales']) || !is_array($options['locales'])) {
            throw InvalidArgumentException::arrayOfArraysExpected(
                $field,
                'filter',
                'completeness',
                print_r($options, true)
            );
        }
    }
}
