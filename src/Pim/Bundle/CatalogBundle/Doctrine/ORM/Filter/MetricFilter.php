<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Metric filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var MeasureManager */
    protected $measureManager;

    /** @var MeasureConverter */
    protected $measureConverter;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param MeasureManager           $measureManager
     * @param MeasureConverter         $measureConverter
     * @param array                    $supportedAttributeTypes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        MeasureManager $measureManager,
        MeasureConverter $measureConverter,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->measureManager = $measureManager;
        $this->measureConverter = $measureConverter;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null,
        $options = []
    ) {
        $this->checkLocaleAndScope($attribute, $locale, $scope);

        if (Operators::IS_EMPTY === $operator || Operators::IS_NOT_EMPTY === $operator) {
            $this->addEmptyTypeFilter($attribute, $operator, $locale, $scope);
        } else {
            $this->checkValue($attribute, $value);
            $value = $this->convertValue($attribute, $value);
            $this->addFilter($attribute, $operator, $value, $locale, $scope);
        }

        return $this;
    }

    /**
     * Add empty or not empty filter to the qb
     *
     * @param AttributeInterface $attribute
     * @param string             $operator
     * @param string             $locale
     * @param string             $scope
     */
    protected function addEmptyTypeFilter(
        AttributeInterface $attribute,
        $operator,
        $locale = null,
        $scope = null
    ) {
        $backendType = $attribute->getBackendType();
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());
        $joinCondition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        $this->qb->leftJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $joinCondition
        );

        $joinAliasOpt = $this->getUniqueAlias('filterM' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'baseData');
        $whereCondition = $this->prepareCriteriaCondition($backendField, $operator, null);

        $this->qb->leftJoin($joinAlias . '.' . $backendType, $joinAliasOpt);
        $this->qb->andWhere($whereCondition);
    }

    /**
     * Add filter to the query
     *
     * @param AttributeInterface $attribute
     * @param string             $operator
     * @param string             $value
     * @param string             $locale
     * @param string             $scope
     */
    protected function addFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null
    ) {
        $backendType = $attribute->getBackendType();
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());

        // inner join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        $this->qb->innerJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        $joinAliasOpt = $this->getUniqueAlias('filterM' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'baseData');
        $condition = $this->prepareCriteriaCondition($backendField, $operator, $value);
        $this->qb->innerJoin($joinAlias . '.' . $backendType, $joinAliasOpt, 'WITH', $condition);
    }

    /**
     * Check if value is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
     */
    protected function checkValue(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($attribute->getCode(), static::class, $data);
        }

        if (!array_key_exists('amount', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'amount',
                static::class,
                $data
            );
        }

        if (!array_key_exists('unit', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'unit',
                static::class,
                $data
            );
        }

        if (null !== $data['amount'] && !is_numeric($data['amount'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "amount" has to be a numeric, "%s" given', gettype($data['amount'])),
                static::class,
                $data
            );
        }

        if (!is_string($data['unit'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "unit" has to be a string, "%s" given', gettype($data['unit'])),
                static::class,
                $data
            );
        }

        if (!array_key_exists(
            $data['unit'],
            $this->measureManager->getUnitSymbolsForFamily($attribute->getMetricFamily())
        )) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'unit',
                sprintf(
                    'The unit does not exist in the attribute\'s family "%s"',
                    $attribute->getMetricFamily()
                ),
                static::class,
                $data['unit']
            );
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $data
     *
     * @return float
     */
    protected function convertValue(AttributeInterface $attribute, array $data)
    {
        $this->measureConverter->setFamily($attribute->getMetricFamily());

        return $this->measureConverter->convertBaseToStandard($data['unit'], $data['amount']);
    }
}
