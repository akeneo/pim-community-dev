<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\FamilyFilter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Elasticsearch\QueryString;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Text filter for an Elasticsearch query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var FamilyFilter */
    private $familyFilter;

    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        FamilyFilter $familyFilter,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->familyFilter = $familyFilter;
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
            $escapedValue = QueryString::escapeValue($value);
        }

        $attributePath = $this->getAttributePath($attribute, $locale, $channel);

        switch ($operator) {
            case Operators::STARTS_WITH:
                $clause = [
                    'query_string' => [
                        'default_field' => $attributePath,
                        'query'         => $escapedValue . '*',
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::CONTAINS:
                $clause = [
                    'query_string' => [
                        'default_field' => $attributePath,
                        'query'         => '*' . $escapedValue . '*',
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::DOES_NOT_CONTAIN:
                $mustNotClause = [
                    'query_string' => [
                        'default_field' => $attributePath,
                        'query'         => '*' . $escapedValue . '*',
                    ],
                ];
                $filterClause = [
                    'exists' => ['field' => $attributePath],
                ];

                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;

            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $attributePath => $value,
                    ],
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

            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($clause);

                $this->familyFilter->setQueryBuilder($this->searchQueryBuilder);
                $this->familyFilter->addFieldFilter('family', Operators::IS_NOT_EMPTY, 'not_used_value');
                break;

            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Check if the value is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $value
     */
    protected function checkValue(AttributeInterface $attribute, $value)
    {
        if (!is_string($value) && null !== $value) {
            throw InvalidPropertyTypeException::stringExpected($attribute->getCode(), static::class, $value);
        }
    }
}
