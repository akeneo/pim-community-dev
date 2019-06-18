<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Date filter for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d';
    const HUMAN_DATETIME_FORMAT = "yyyy-mm-dd";

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array $supportedAttributeTypes
     * @param array $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
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
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $attributeCode = $attribute->getCode();

        $this->checkLocaleAndChannel($attribute, $locale, $channel);
        $this->checkValue($operator, $attributeCode, $value);

        $attributePath = $this->getAttributePath($attribute, $locale, $channel);

        switch ($operator) {
            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $attributePath => $this->getFormattedDate($attributeCode, $value)
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::LOWER_THAN:
                $clause = [
                    'range' => [
                        $attributePath => [
                            'lt' => $this->getFormattedDate($attributeCode, $value),
                        ]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::GREATER_THAN:
                $clause = [
                    'range' => [
                        $attributePath => [
                            'gt' => $this->getFormattedDate($attributeCode, $value),
                        ]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::BETWEEN:
                $values = array_values($value);
                $clause = [
                    'range' => [
                        $attributePath => [
                            'gte' => $this->getFormattedDate($attributeCode, $values[0]),
                            'lte' => $this->getFormattedDate($attributeCode, $values[1]),
                        ]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::NOT_BETWEEN:
                $values = array_values($value);
                $betweenClause = [
                    'range' => [
                        $attributePath => [
                            'gte' => $this->getFormattedDate($attributeCode, $values[0]),
                            'lte' => $this->getFormattedDate($attributeCode, $values[1]),
                        ]
                    ]
                ];

                $existsClause = [
                    'exists' => ['field' => $attributePath]
                ];

                $this->searchQueryBuilder->addMustNot($betweenClause);
                $this->searchQueryBuilder->addFilter($existsClause);

                break;
            case Operators::IS_EMPTY:
                $existsClause = [
                    'exists' => ['field' => $attributePath]
                ];
                $this->searchQueryBuilder->addMustNot($existsClause);

                $familyExistsClause = [
                    'exists' => ['field' => 'family.code']
                ];
                $this->searchQueryBuilder->addFilter($familyExistsClause);

                break;
            case Operators::IS_NOT_EMPTY:
                $existsClause = [
                    'exists' => ['field' => $attributePath]
                ];

                $this->searchQueryBuilder->addFilter($existsClause);

                break;
            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'term' => [
                        $attributePath => $this->getFormattedDate($attributeCode, $value)
                    ]
                ];

                $existsClause = [
                    'exists' => ['field' => $attributePath]
                ];

                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($existsClause);

                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * @param string $operator
     * @param string $field
     * @param string|array|\DateTime $value
     */
    protected function checkValue($operator, $field, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
            case Operators::LOWER_THAN:
            case Operators::GREATER_THAN:
            case Operators::NOT_EQUAL:
                FieldFilterHelper::checkDateTime(
                    $field,
                    $value,
                    static::DATETIME_FORMAT,
                    static::HUMAN_DATETIME_FORMAT,
                    static::class
                );

                break;
            case Operators::BETWEEN:
            case Operators::NOT_BETWEEN:
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                }

                if (2 !== count($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('should contain 2 strings with the format "%s"', static::HUMAN_DATETIME_FORMAT),
                        static::class,
                        $value
                    );
                }

                foreach ($value as $singleValue) {
                    FieldFilterHelper::checkDateTime(
                        $field,
                        $singleValue,
                        static::DATETIME_FORMAT,
                        static::HUMAN_DATETIME_FORMAT,
                        static::class
                    );
                }

                break;
            case Operators::IS_EMPTY:
            case Operators::IS_NOT_EMPTY:
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }
    }

    /**
     * @param string $field
     * @param string|\DateTime $value
     *
     * @return string
     */
    protected function getFormattedDate($field, $value)
    {
        $dateTime = $value;

        if (!$dateTime instanceof \DateTime) {
            $dateTime = \DateTime::createFromFormat(static::DATETIME_FORMAT, $dateTime);

            if (false === $dateTime || 0 < $dateTime->getLastErrors()['warning_count']) {
                throw InvalidPropertyException::dateExpected(
                    $field,
                    static::HUMAN_DATETIME_FORMAT,
                    static::class,
                    $value
                );
            }
        }

        return $dateTime->format(static::DATETIME_FORMAT);
    }
}
