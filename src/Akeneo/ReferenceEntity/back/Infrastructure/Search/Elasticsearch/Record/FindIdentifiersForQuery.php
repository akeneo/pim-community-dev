<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ValueKey\SqlGetValueKeyForAttributeChannelAndLocale;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Elasticsearch\QueryString;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindIdentifiersForQuery implements FindIdentifiersForQueryInterface
{
    private const INDEX_TYPE = 'pimee_reference_entity_record';
    private const ATTRIBUTE_FILTER_FIELD = 'values.';

    /** @var Client */
    private $recordClient;

    /** @var FindRequiredValueKeyCollectionForChannelAndLocalesInterface */
    private $findRequiredValueKeyCollectionForChannelAndLocale;

    /** @var SqlGetValueKeyForAttributeChannelAndLocale */
    private $getValueKeyForAttributeChannelAndLocale;

    /** @var AttributeRepositoryInterface  */
    private $attributeRepository;

    /** @var FindIdentifiersByReferenceEntityAndCodesInterface  */
    private $findIdentifiersByReferenceEntityAndCodes;

    public function __construct(
        Client $recordClient,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocale,
        SqlGetValueKeyForAttributeChannelAndLocale $getValueKeyForAttributeChannelAndLocale,
        AttributeRepositoryInterface $attributeRepository,
        FindIdentifiersByReferenceEntityAndCodesInterface $findIdentifiersByReferenceEntityAndCodes
    ) {
        $this->recordClient = $recordClient;
        $this->findRequiredValueKeyCollectionForChannelAndLocale = $findRequiredValueKeyCollectionForChannelAndLocale;
        $this->getValueKeyForAttributeChannelAndLocale = $getValueKeyForAttributeChannelAndLocale;
        $this->attributeRepository = $attributeRepository;
        $this->findIdentifiersByReferenceEntityAndCodes = $findIdentifiersByReferenceEntityAndCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function find(RecordQuery $recordQuery): IdentifiersForQueryResult
    {
        $elasticSearchQuery = $this->getElasticSearchQuery($recordQuery);
        $matches = $this->recordClient->search(self::INDEX_TYPE, $elasticSearchQuery);
        $identifiers = $this->getIdentifiers($matches);
        $queryResult = new IdentifiersForQueryResult($identifiers, $matches['hits']['total']);

        return $queryResult;
    }

    private function getElasticSearchQuery(RecordQuery $recordQuery): array
    {
        $referenceEntityCode = $recordQuery->getFilter('reference_entity')['value'];
        $fullTextFilter = ($recordQuery->hasFilter('full_text')) ? $recordQuery->getFilter('full_text') : null;
        $codeLabelFilter = ($recordQuery->hasFilter('code_label')) ? $recordQuery->getFilter('code_label') : null;
        $codeFilter = ($recordQuery->hasFilter('code')) ? $recordQuery->getFilter('code') : null;
        $completeFilter = ($recordQuery->hasFilter('complete')) ? $recordQuery->getFilter('complete') : null;
        $updatedFilter = ($recordQuery->hasFilter('updated')) ? $recordQuery->getFilter('updated') : null;
        $attributeFilters = ($recordQuery->hasFilter('values.*')) ? $recordQuery->getValueFilters() : [];

        $query = [
            '_source' => '_id',
            'size'    => $recordQuery->getSize(),
            'query'   => [
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
                    'default_field' => sprintf('record_full_text_search.%s.%s', $recordQuery->getchannel(),
                        $recordQuery->getlocale()),
                    'query'         => $terms,
                ],
            ];
        }

        if (null !== $codeLabelFilter && !empty($codeLabelFilter['value'])) {
            $terms = $this->getTerms($codeLabelFilter);
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'default_field' => sprintf('record_code_label_search.%s', $recordQuery->getlocale()),
                    'query'         => $terms,
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

        if (null !== $updatedFilter && !empty($updatedFilter['value'] && '>' === $updatedFilter['operator'])) {
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'range' => [
                   'updated_at' => ['gt' => $this->getFormattedDate($updatedFilter['value'])]
                ]
            ];
        }

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

                        $value = array_values(array_map(function (RecordIdentifier $recordIdentifier) {
                            return (string) $recordIdentifier;
                        }, $recordIdentifiers));
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
        $wildcardTerms = array_map(function (string $term) {
            return sprintf('*%s*', QueryString::escapeValue($term));
        }, $terms);
        $query = implode(' AND ', $wildcardTerms);

        return $query;
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
        $channel = isset($completeFilter['context']['channel']) ? $completeFilter['context']['channel'] : $recordQuery->getChannel();
        $locales = isset($completeFilter['context']['locales']) ? $completeFilter['context']['locales'] : [$recordQuery->getLocale()];

        $requiredValueKeys = $this->getRequiredValueKeys(
            $referenceEntityCode,
            ChannelIdentifier::fromCode($channel),
            LocaleIdentifierCollection::fromNormalized($locales)
        );
        if (true === $completeFilter['value']) {
            $clauses = array_map(function (string $requiredValueKey) {
                return [
                    'exists' => [
                        'field' => sprintf('complete_value_keys.%s', $requiredValueKey),
                    ],
                ];
            }, $requiredValueKeys->normalize());
            $query['query']['constant_score']['filter']['bool']['filter'] = array_merge($query['query']['constant_score']['filter']['bool']['filter'],
                $clauses);
        }
        if (false === $completeFilter['value']) {
            $clauses = array_map(function (string $requiredValueKey) {
                return [
                    'bool' => [
                        'must_not' => [
                            [
                                'exists' => [
                                    'field' => sprintf('complete_value_keys.%s', $requiredValueKey),
                                ],
                            ],
                        ],
                    ],
                ];
            }, $requiredValueKeys->normalize());
            $query['query']['constant_score']['filter']['bool']['should'] = array_merge($query['query']['constant_score']['filter']['bool']['should'] ?? [],
                $clauses);
        }

        return $query;
    }

    /**
     * @param array $matches
     *
     * @return string[]
     */
    private function getIdentifiers(array $matches): array
    {
        $identifiers = array_map(function (array $hit) {
            return $hit['_id'];
        }, $matches['hits']['hits']);

        return $identifiers;
    }
}
