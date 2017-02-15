<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
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

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $value);
            $value = $this->convertValue($attribute, $value);
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf('%s.%s.baseData', ProductQueryUtility::NORMALIZED_FIELD, $field);

        $this->applyFilter($operator, $field, $value);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string $operator
     * @param string $fieldData
     * @param float  $data
     */
    protected function applyFilter($operator, $fieldData, $data)
    {
        switch ($operator) {
            case Operators::EQUALS:
                $this->qb->field($fieldData)->equals($data);
                break;
            case Operators::NOT_EQUAL:
                $this->qb->field($fieldData)->exists(true);
                $this->qb->field($fieldData)->notEqual($data);
                break;
            case Operators::LOWER_THAN:
                $this->qb->field($fieldData)->lt($data);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $this->qb->field($fieldData)->lte($data);
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($fieldData)->gt($data);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $this->qb->field($fieldData)->gte($data);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($fieldData)->exists(false);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($fieldData)->exists(true);
                break;
        }
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

        return (float) $this->measureConverter->convertBaseToStandard($data['unit'], $data['amount']);
    }
}
