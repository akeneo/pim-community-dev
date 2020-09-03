<?php

// @todo pull-up 4.0: remove this class and its service definition

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\IsEmpty;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;

class MetricFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    const PATH_SUFFIX = 'base_data';

    /** @var MeasureManager */
    protected $measureManager;

    /** @var MeasureConverter */
    protected $measureConverter;

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
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkLocaleAndChannel($attribute, $locale, $channel);

        $attributePath = $this->getAttributePath($attribute, $locale, $channel) . '.' . self::PATH_SUFFIX;

        switch ($operator) {
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath
                    ]
                ];
                $this->searchQueryBuilder->addMustNot($clause);

                $attributeInEntityClauses = [
                    [
                        'terms' => [
                            self::ATTRIBUTES_FOR_THIS_LEVEL_ES_ID => [$attribute->getCode()],
                        ],
                    ],
                    [
                        'terms' => [
                            self::ATTRIBUTES_OF_ANCESTORS_ES_ID => [$attribute->getCode()],
                        ],
                    ]
                ];
                $this->searchQueryBuilder->addFilter(
                    [
                        'bool' => [
                            'should' => $attributeInEntityClauses,
                            'minimum_should_match' => 1,
                        ],
                    ]
                );
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }
}
