<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersByAssetFamilyAndCodesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\AssetManager\Domain\Query\ValueKey\GetValueKeyForAttributeChannelAndLocaleInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Elasticsearch\QueryString;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class AssetQueryBuilder implements AssetQueryBuilderInterface
{
    private const ATTRIBUTE_FILTER_FIELD = 'values.';

    public function __construct(
        private FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocale,
        private GetValueKeyForAttributeChannelAndLocaleInterface $getValueKeyForAttributeChannelAndLocale,
        private AttributeRepositoryInterface $attributeRepository,
        private FindIdentifiersByAssetFamilyAndCodesInterface $findIdentifiersByAssetFamilyAndCodes,
    ) {
    }

    public function buildFromQuery(AssetQuery $assetQuery, $source): array
    {
        $assetFamilyCode = $assetQuery->getFilter('asset_family')['value'];
        $fullTextFilter = ($assetQuery->hasFilter('full_text')) ? $assetQuery->getFilter('full_text') : null;
        $codeLabelFilter = ($assetQuery->hasFilter('code_label')) ? $assetQuery->getFilter('code_label') : null;
        $codeFilter = ($assetQuery->hasFilter('code')) ? $assetQuery->getFilter('code') : null;
        $completeFilter = ($assetQuery->hasFilter('complete')) ? $assetQuery->getFilter('complete') : null;
        $updatedFilters = ($assetQuery->hasFilter('updated')) ? $assetQuery->getFilters('updated') : [];
        $attributeFilters = ($assetQuery->hasFilter('values.*')) ? $assetQuery->getValueFilters() : [];

        $query = [
            '_source' => $source,
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

        if (null !== $fullTextFilter && '' !== $fullTextFilter['value']) {
            $terms = $this->getTerms($fullTextFilter);
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'default_field' => sprintf(
                        'asset_full_text_search.%s.%s',
                        $assetQuery->getchannel(),
                        $assetQuery->getlocale()
                    ),
                    'query'         => $terms,
                ],
            ];
        }

        if (null !== $codeLabelFilter && '' !== $codeLabelFilter['value']) {
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
                    'code' => array_values($codeFilter['value']),
                ],
            ];
        }

        if (null !== $codeFilter && !empty($codeFilter['value']) && 'IN' === $codeFilter['operator']) {
            $query['query']['constant_score']['filter']['bool']['must'][] = [
                'terms' => [
                    'code' => array_values($codeFilter['value']),
                ],
            ];
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
                    if (in_array($attribute->getType(), ['asset', 'asset_collection'])) {
                        $assetIdentifiers = $this->findIdentifiersByAssetFamilyAndCodes->find(
                            $attribute->getAssetType(),
                            $attributeFilter['value']
                        );

                        $value = array_values(array_map(fn (AssetIdentifier $assetIdentifier) => (string) $assetIdentifier, $assetIdentifiers));
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
        $wildcardTerms = array_map(fn (string $term) => sprintf('*%s*', QueryString::escapeValue($term)), $terms);

        return implode(' AND ', $wildcardTerms);
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
        $channel = $completeFilter['context']['channel'] ?? $assetQuery->getChannel();
        $locales = $completeFilter['context']['locales'] ?? [$assetQuery->getLocale()];

        $requiredValueKeys = $this->getRequiredValueKeys(
            $assetFamilyCode,
            ChannelIdentifier::fromCode($channel),
            LocaleIdentifierCollection::fromNormalized($locales)
        );
        if (true === $completeFilter['value']) {
            $clauses = array_map(fn (string $requiredValueKey) => [
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
            $clauses = array_map(fn (string $requiredValueKey) => [
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
