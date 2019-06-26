<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Number filter for an Elasticsearch query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
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

        $this->checkLocaleAndChannel($attribute, $locale, $channel);

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $value);
        }

        $attributePath = $this->getAttributePath($attribute, $locale, $channel);

        switch ($operator) {
            case Operators::LOWER_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['lt' => $value]
                    ]
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['lte' => $value]
                    ]
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $attributePath => $value
                    ]
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'term' => [
                        $attributePath => $value,
                    ],
                ];
                $filterClause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['gte' => $value]
                    ]
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::GREATER_THAN:
                $clause = [
                    'range' => [
                        $attributePath => ['gt' => $value]
                    ]
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath
                    ]
                ];
                $this->searchQueryBuilder->addMustNot($clause);

                $familyExistsClause = [
                    'exists' => ['field' => 'family.code']
                ];
                $this->searchQueryBuilder->addFilter($familyExistsClause);
                break;
            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath
                    ]
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Checks that the value is a number.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $value
     */
    protected function checkValue(AttributeInterface $attribute, $value)
    {
        if (!is_numeric($value) && null !== $value) {
            throw InvalidPropertyTypeException::numericExpected($attribute->getCode(), static::class, $value);
        }
    }
}
