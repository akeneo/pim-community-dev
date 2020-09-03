<?php

// @todo pull-up 4.0: remove this class and its service definition

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\IsEmpty;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;

/**
 * Proposal metric filter for an Elasticsearch query
 */
class MetricFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    const PATH_SUFFIX = 'base_data';

    /** @var MeasureManager */
    protected $measureManager;

    /** @var MeasureConverter */
    protected $measureConverter;

    public function __construct(
        ProposalAttributePathResolver $attributePathResolver,
        MeasureManager $measureManager,
        MeasureConverter $measureConverter,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->measureManager = $measureManager;
        $this->measureConverter = $measureConverter;
        $this->supportedOperators = $supportedOperators;
        $this->attributePathResolver = $attributePathResolver;
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

        $attributePaths = $this->attributePathResolver->getAttributePaths($attribute);

        switch ($operator) {
            case Operators::IS_EMPTY:
                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => [
                            'field' => $attributePath . '.' . self::PATH_SUFFIX
                        ]
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
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
