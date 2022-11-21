<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;

/**
 * Option filter for an Elasticsearch query
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var AttributeOptionRepositoryInterface */
    protected $attributeOptionRepository;

    public function __construct(
        ElasticsearchFilterValidator $filterValidator,
        AttributeOptionRepositoryInterface $attributeOptionRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->filterValidator = $filterValidator;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $values,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkLocaleAndChannel($attribute, $locale, $channel);

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $values);
        }

        $attributePath = $this->getAttributePath($attribute, $locale, $channel);

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        $attributePath => $values,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
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

            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_IN_LIST:
                $mustNotClause = [
                    'terms' => [
                        $attributePath => $values,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                break;

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Check if values are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $values
     *
     * @throws ObjectNotFoundException
     */
    protected function checkValue(AttributeInterface $attribute, $values)
    {
        FieldFilterHelper::checkArray($attribute->getCode(), $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($attribute->getCode(), $value, static::class);
        }

        $attributeOptions = $this->attributeOptionRepository->findCodesByIdentifiers($attribute->getCode(), $values);
        $optionCodes = array_map(
            function ($attributeOptions) {
                return $attributeOptions['code'];
            },
            $attributeOptions
        );

        $unexistingValues = array_udiff($values, $optionCodes, 'strcasecmp');
        if (count($unexistingValues) > 0) {
            throw new ObjectNotFoundException(
                sprintf(
                    'Object "%s" with code "%s" does not exist',
                    $attribute->getBackendType(),
                    reset($unexistingValues)
                )
            );
        }
    }
}
