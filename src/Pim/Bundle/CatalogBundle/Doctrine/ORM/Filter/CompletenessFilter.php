<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\CompletenessJoin;
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
    /** @var array Allow to map complex operators to simpler operators */
    protected $operatorsMapping = [
        Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES => Operators::GREATER_OR_EQUAL_THAN,
        Operators::GREATER_THAN_ON_ALL_LOCALES           => Operators::GREATER_THAN,
        Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES   => Operators::LOWER_OR_EQUAL_THAN,
        Operators::LOWER_THAN_ON_ALL_LOCALES             => Operators::LOWER_THAN,
    ];

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
     *
     * If locale is omitted, all products having a matching completeness for
     * the specified scope (no matter on which locale) will be selected.
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $this->checkScopeAndValue($field, $scope, $value);

        if (array_key_exists($operator, $this->operatorsMapping)) {
            $this->checkOptions($field, $options);

            foreach ($options['locales'] as $localeCode) {
                $this->applyFilter($this->operatorsMapping[$operator], $value, $localeCode, $scope);
            }
        } else {
            $this->applyFilter($operator, $value, $locale, $scope);
        }

        return $this;
    }

    /**
     * @param string      $operator
     * @param string      $value
     * @param string|null $locale
     * @param string|null $scope
     */
    protected function applyFilter($operator, $value, $locale = null, $scope = null)
    {
        $joinAlias = $this->getUniqueAlias('filterCompleteness');
        $field = $joinAlias . '.ratio';
        $util = new CompletenessJoin($this->qb);
        $util->addJoins($joinAlias, $locale, $scope);

        $this->qb->andWhere($this->prepareCriteriaCondition($field, $operator, $value));
    }

    /**
     * Check if scope and value are valid
     *
     * @param string $field
     * @param mixed  $scope
     * @param mixed  $value
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
     */
    protected function checkScopeAndValue($field, $scope, $value)
    {
        if (!is_numeric($value)) {
            throw InvalidPropertyTypeException::numericExpected($field, static::class, $value);
        }

        if (null === $scope) {
            throw InvalidPropertyException::dataExpected($field, 'a valid scope', static::class);
        }
    }

    /**
     * Check if options are valid for complex operators
     *      GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES
     *      GREATER_THAN_ON_ALL_LOCALES
     *      LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES
     *      LOWER_THAN_ON_ALL_LOCALES
     *
     * @param string $field
     * @param array  $options
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkOptions($field, array $options)
    {
        if (!array_key_exists('locales', $options)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $field,
                'locales',
                static::class,
                $options
            );
        }

        if (!isset($options['locales']) || !is_array($options['locales'])) {
            throw InvalidPropertyTypeException::arrayOfArraysExpected(
                $field,
                static::class,
                $options
            );
        }
    }
}
