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

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersByAssetFamilyAndCodesInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersForQueryInterface;
use Akeneo\AssetManager\Domain\Query\Asset\IdentifiersForQueryResult;
use Akeneo\AssetManager\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\ValueKey\SqlGetValueKeyForAttributeChannelAndLocale;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Elasticsearch\QueryString;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindIdentifiersForQuery implements FindIdentifiersForQueryInterface
{
    private const ATTRIBUTE_FILTER_FIELD = 'values.';

    /** @var Client */
    private $assetClient;

    /** @var FindRequiredValueKeyCollectionForChannelAndLocalesInterface */
    private $findRequiredValueKeyCollectionForChannelAndLocale;

    /** @var SqlGetValueKeyForAttributeChannelAndLocale */
    private $getValueKeyForAttributeChannelAndLocale;

    /** @var AttributeRepositoryInterface  */
    private $attributeRepository;

    /** @var FindIdentifiersByAssetFamilyAndCodesInterface  */
    private $findIdentifiersByAssetFamilyAndCodes;

    public function __construct(
        Client $assetClient,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocale,
        SqlGetValueKeyForAttributeChannelAndLocale $getValueKeyForAttributeChannelAndLocale,
        AttributeRepositoryInterface $attributeRepository,
        FindIdentifiersByAssetFamilyAndCodesInterface $findIdentifiersByAssetFamilyAndCodes
    ) {
        $this->assetClient = $assetClient;
        $this->findRequiredValueKeyCollectionForChannelAndLocale = $findRequiredValueKeyCollectionForChannelAndLocale;
        $this->getValueKeyForAttributeChannelAndLocale = $getValueKeyForAttributeChannelAndLocale;
        $this->attributeRepository = $attributeRepository;
        $this->findIdentifiersByAssetFamilyAndCodes = $findIdentifiersByAssetFamilyAndCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetQuery $assetQuery): IdentifiersForQueryResult
    {
        $elasticSearchQuery = $this->getElasticSearchQuery($assetQuery);
        $matches = $this->assetClient->search($elasticSearchQuery);
        $identifiers = $this->getIdentifiers($matches);
        $lastSortValue = $this->getLastSortValue($matches);
        $queryResult = new IdentifiersForQueryResult($identifiers, $matches['hits']['total']['value'], $lastSortValue);

        return $queryResult;
    }

    private function getElasticSearchQuery(AssetQuery $assetQuery): array
    {
        $assetFamilyCode = $assetQuery->getFilter('asset_family')['value'];
        $fullTextFilter = ($assetQuery->hasFilter('full_text')) ? $assetQuery->getFilter('full_text') : null;
        $codeLabelFilter = ($assetQuery->hasFilter('code_label')) ? $assetQuery->getFilter('code_label') : null;
        $codeFilter = ($assetQuery->hasFilter('code')) ? $assetQuery->getFilter('code') : null;
        $completeFilter = ($assetQuery->hasFilter('complete')) ? $assetQuery->getFilter('complete') : null;
        $updatedFilter = ($assetQuery->hasFilter('updated')) ? $assetQuery->getFilter('updated') : null;
        $attributeFilters = ($assetQuery->hasFilter('values.*')) ? $assetQuery->getValueFilters() : [];

        $query = [
            '_source' => '_id',
            'size'    => $assetQuery->getSize(),
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'asset_family_code' => $assetFamilyCode,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        if ($assetQuery->isPaginatedUsingOffset()) {
            $query['from'] = $assetQuery->getSize() * $assetQuery->getPage();
            $query['sort'] = ['updated_at' => 'desc'];
        }

        if ($assetQuery->isPaginatedUsingSearchAfter()) {
            if (null !== $assetQuery->getSearchAfterCode()) {
                $query['search_after'] = [$assetQuery->getSearchAfterCode()];
            }
            $query['sort'] = ['code' => 'asc'];
        }

        if (null !== $fullTextFilter && !empty($fullTextFilter['value'])) {
            $terms = $this->getTerms($fullTextFilter);
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'default_field' => sprintf('asset_full_text_search.%s.%s', $assetQuery->getchannel(),
                        $assetQuery->getlocale()),
                    'query'         => $terms,
                ],
            ];
        }

        if (null !== $codeLabelFilter && !empty($codeLabelFilter['value'])) {
            $terms = $this->getTerms($codeLabelFilter);
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'default_field' => sprintf('asset_code_label_search.%s', $assetQuery->getlocale()),
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
                    if (in_array($attribute->getType(), ['asset', 'asset_collection'])) {
                        $assetIdentifiers = $this->findIdentifiersByAssetFamilyAndCodes->find(
                            $attribute->getAssetType(),
                            $attributeFilter['value']
                        );

                        $value = array_values(array_map(function (AssetIdentifier $assetIdentifier) {
                            return (string) $assetIdentifier;
                        }, $assetIdentifiers));
                    }

                    $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
                        AttributeIdentifier::fromString($attributeIdentifier),
                        ChannelIdentifier::fromCode($assetQuery->getchannel()),
                        LocaleIdentifier::fromCode($assetQuery->getLocale())
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
            $query = $this->getCompleteFilterQuery($assetQuery, $assetFamilyCode, $completeFilter, $query);
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
        $assetFamilyCode,
        ChannelIdentifier $channel,
        LocaleIdentifierCollection $locales
    ): ValueKeyCollection {
        return $this->findRequiredValueKeyCollectionForChannelAndLocale->find(
            AssetFamilyIdentifier::fromString($assetFamilyCode),
            $channel,
            $locales
        );
    }

    private function getCompleteFilterQuery(AssetQuery $assetQuery, $assetFamilyCode, $completeFilter, $query)
    {
        $channel = isset($completeFilter['context']['channel']) ? $completeFilter['context']['channel'] : $assetQuery->getChannel();
        $locales = isset($completeFilter['context']['locales']) ? $completeFilter['context']['locales'] : [$assetQuery->getLocale()];

        $requiredValueKeys = $this->getRequiredValueKeys(
            $assetFamilyCode,
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
            $query['query']['constant_score']['filter']['bool']['minimum_should_match'] = 1;
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

    private function getLastSortValue(array $matches): ?array
    {
        $lastHit = end($matches['hits']['hits']);

        return $lastHit['sort'] ?? null;
    }
}
