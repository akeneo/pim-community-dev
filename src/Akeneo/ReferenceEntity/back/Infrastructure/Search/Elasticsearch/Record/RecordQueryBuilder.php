<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByReferenceEntityAndCodesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Query\ValueKey\GetValueKeyForAttributeChannelAndLocaleInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Elasticsearch\QueryString;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class RecordQueryBuilder implements RecordQueryBuilderInterface
{
    private const ATTRIBUTE_FILTER_FIELD = 'values.';

    public function __construct(
        private FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocale,
        private GetValueKeyForAttributeChannelAndLocaleInterface $getValueKeyForAttributeChannelAndLocale,
        private AttributeRepositoryInterface $attributeRepository,
        private FindIdentifiersByReferenceEntityAndCodesInterface $findIdentifiersByReferenceEntityAndCodes
    ) {
    }

    public function buildFromQuery(RecordQuery $recordQuery, $source): array
    {
        $referenceEntityCode = $recordQuery->getFilter('reference_entity')['value'];
        $fullTextFilter = ($recordQuery->hasFilter('full_text')) ? $recordQuery->getFilter('full_text') : null;
        $codeLabelFilter = ($recordQuery->hasFilter('code_label')) ? $recordQuery->getFilter('code_label') : null;
        $codeFilter = ($recordQuery->hasFilter('code')) ? $recordQuery->getFilter('code') : null;
        $completeFilter = ($recordQuery->hasFilter('complete')) ? $recordQuery->getFilter('complete') : null;
        $updatedFilters = ($recordQuery->hasFilter('updated')) ? $recordQuery->getFiltersByField('updated') : [];
        $attributeFilters = ($recordQuery->hasFilter('values.*')) ? $recordQuery->getValueFilters() : [];

        $query = [
            '_source' => $source,
            'size' => $recordQuery->getSize(),
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'reference_entity_code' => $referenceEntityCode,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        if ($recordQuery->isPaginatedUsingOffset()) {
            $query['from'] = $recordQuery->getSize() * $recordQuery->getPage();
            $query['sort'] = ['updated_at' => 'desc'];
        }

        if ($recordQuery->isPaginatedUsingSearchAfter()) {
            if (null !== $recordQuery->getSearchAfterCode()) {
                $query['search_after'] = [$recordQuery->getSearchAfterCode()];
            }
            $query['sort'] = ['code' => 'asc'];
        }

        if (null !== $fullTextFilter && !empty($fullTextFilter['value'])) {
            $terms = $this->getTerms($fullTextFilter);
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'default_field' => sprintf(
                        'record_full_text_search.%s.%s',
                        $recordQuery->getchannel(),
                        $recordQuery->getlocale()
                    ),
                    'query' => $terms,
                ],
            ];
        }

        if (null !== $codeLabelFilter && !empty($codeLabelFilter['value'])) {
            $terms = $this->getTerms($codeLabelFilter);
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'fields' => [
                        sprintf('record_code_label_search.%s', $recordQuery->getlocale()),
                        'code'
                    ],
                    'query' => $terms,
                ],
            ];
        }

        if (null !== $codeFilter && !empty($codeFilter['value']) && 'NOT IN' === $codeFilter['operator']) {
            $query['query']['constant_score']['filter']['bool']['must_not'][] = [
                'terms' => [
                    'code' => $codeFilter['value'],
                ],
            ];
        }

        if (null !== $codeFilter && !empty($codeFilter['value']) && 'IN' === $codeFilter['operator']) {
            $query['query']['constant_score']['filter']['bool']['must'][] = [
                'terms' => [
                    'code' => $codeFilter['value'],
                ],
            ];
            // IN filter codes are alphabetically sorted and we must return the same order
            $query['sort'] = ['code' => 'asc'];
        }

        $query['query']['constant_score']['filter']['bool'] = array_reduce(
            $updatedFilters,
            function (array $query, array $filter): array {
                if (empty($filter['value'])) {
                    return $query;
                }
                switch ($filter['operator']) {
                    case '<':
                        $query['filter'][] = ['range' => ['updated_at' => [
                            'lt' => $this->getFormattedDate($filter['value']),
                        ]]];
                        break;
                    case '>':
                        $query['filter'][] = ['range' => ['updated_at' => [
                            'gt' => $this->getFormattedDate($filter['value']),
                        ]]];
                        break;
                    case 'BETWEEN':
                        $query['filter'][] = ['range' => ['updated_at' => [
                            'gt' => $this->getFormattedDate($filter['value'][0]),
                            'lt' => $this->getFormattedDate($filter['value'][1]),
                        ]]];
                        break;
                    case 'NOT BETWEEN':
                        $query['must_not'][] = ['range' => ['updated_at' => [
                            'gt' => $this->getFormattedDate($filter['value'][0]),
                            'lt' => $this->getFormattedDate($filter['value'][1]),
                        ]]];
                        break;
                    case 'SINCE LAST N DAYS':
                        $query['filter'][] = ['range' => ['updated_at' => [
                            'gt' => $this->getFormattedDate(sprintf('%s days ago', $filter['value'])),
                        ]]];
                        break;
                }
                return $query;
            },
            $query['query']['constant_score']['filter']['bool']
        );

        if (!empty($attributeFilters)) {
            foreach ($attributeFilters as $attributeFilter) {
                if (!empty($attributeFilter['value'] && 'IN' === $attributeFilter['operator'])) {
                    // As the attribute identifier filter will have all the time the same structure values.*. We could extract only the last part of the string with a substr from the dot.
                    $attributeIdentifier = substr($attributeFilter['field'], strlen(self::ATTRIBUTE_FILTER_FIELD));
                    $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::fromString($attributeIdentifier));

                    $value = $attributeFilter['value'];
                    if (in_array($attribute->getType(), ['record', 'record_collection'])) {
                        $recordIdentifiers = $this->findIdentifiersByReferenceEntityAndCodes->find(
                            $attribute->getRecordType(),
                            $attributeFilter['value']
                        );

                        $value = array_values(array_map(static fn (RecordIdentifier $recordIdentifier) => (string) $recordIdentifier, $recordIdentifiers));
                    }

                    $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
                        AttributeIdentifier::fromString($attributeIdentifier),
                        ChannelIdentifier::fromCode($recordQuery->getchannel()),
                        LocaleIdentifier::fromCode($recordQuery->getLocale())
                    );
                    $path = sprintf('values.%s', (string) $valueKey);

                    $query['query']['constant_score']['filter']['bool']['filter'][] = [
                        'terms' => [
                            $path => $value
                        ]
                    ];
                }
            }
        }

        if (null !== $completeFilter) {
            $query = $this->getCompleteFilterQuery($recordQuery, $referenceEntityCode, $completeFilter, $query);
        }

        return $query;
    }

    private function getFormattedDate(string $updatedDate): int
    {
        $date = new \DateTime($updatedDate);

        return $date->getTimestamp();
    }

    private function getTerms(array $searchFilter): string
    {
        $loweredTerms = strtolower($searchFilter['value']);
        $terms = explode(' ', $loweredTerms);
        $wildcardTerms = array_map(static fn (string $term) => sprintf('*%s*', QueryString::escapeValue($term)), $terms);

        return implode(' AND ', $wildcardTerms);
    }

    private function getRequiredValueKeys(
        $referenceEntityCode,
        ChannelIdentifier $channel,
        LocaleIdentifierCollection $locales
    ): ValueKeyCollection {
        return $this->findRequiredValueKeyCollectionForChannelAndLocale->find(
            ReferenceEntityIdentifier::fromString($referenceEntityCode),
            $channel,
            $locales
        );
    }

    private function getCompleteFilterQuery(RecordQuery $recordQuery, $referenceEntityCode, $completeFilter, $query)
    {
        $channel = $completeFilter['context']['channel'] ?? $recordQuery->getChannel();
        $locales = $completeFilter['context']['locales'] ?? [$recordQuery->getLocale()];

        $requiredValueKeys = $this->getRequiredValueKeys(
            $referenceEntityCode,
            ChannelIdentifier::fromCode($channel),
            LocaleIdentifierCollection::fromNormalized($locales)
        );
        if (true === $completeFilter['value']) {
            $clauses = array_map(static fn (string $requiredValueKey) => [
                'exists' => [
                    'field' => sprintf('complete_value_keys.%s', $requiredValueKey),
                ],
            ], $requiredValueKeys->normalize());
            $query['query']['constant_score']['filter']['bool']['filter'] = array_merge(
                $query['query']['constant_score']['filter']['bool']['filter'],
                $clauses
            );
        }
        if (false === $completeFilter['value']) {
            $clauses = array_map(static fn (string $requiredValueKey) => [
                'bool' => [
                    'must_not' => [
                        [
                            'exists' => [
                                'field' => sprintf('complete_value_keys.%s', $requiredValueKey),
                            ],
                        ],
                    ],
                ],
            ], $requiredValueKeys->normalize());
            $query['query']['constant_score']['filter']['bool']['minimum_should_match'] = 1;
            $query['query']['constant_score']['filter']['bool']['should'] = array_merge(
                $query['query']['constant_score']['filter']['bool']['should'] ?? [],
                $clauses
            );
        }

        return $query;
    }
}
