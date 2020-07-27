<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery implements GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryInterface
{
    private const OPTIONS_MAX_SIZE = 1000;

    /** @var Client */
    private $esClient;

    /** @var GetAttributeOptionSpellcheckQueryInterface */
    private $getAttributeOptionSpellcheckQuery;

    public function __construct(Client $esClient, GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery)
    {
        $this->esClient = $esClient;
        $this->getAttributeOptionSpellcheckQuery = $getAttributeOptionSpellcheckQuery;
    }

    public function byAttribute(Attribute $attribute): int
    {
        $spellchecks = $this->getAttributeOptionSpellcheckQuery->getByAttributeCodeWithSpellingMistakes($attribute->getCode());
        if (empty($spellchecks)) {
            return 0;
        }

        $optionCodes = [];
        $localeCodesToImprove = [];
        foreach ($spellchecks as $spellcheck) {
            $optionCodes[] = strval($spellcheck->getAttributeOptionCode());
            $localeCodesToImprove = array_merge($localeCodesToImprove, $spellcheck->getResult()->getLocalesToImprove());
        }

        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'document_type' => ProductInterface::class
                            ],
                        ],
                        [
                            'bool' => [
                                'should' => $attribute->isLocalizable()
                                    ? $this->buildSearchQueryStringsForLocalizableAttribute($attribute, $optionCodes, array_unique($localeCodesToImprove))
                                    : $this->buildSearchQueryStringsForNotLocalizableAttribute($attribute, $optionCodes)
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->esClient->count($query);

        if (!isset($result['count'])) {
            throw new \RuntimeException(sprintf(
                'Unexpected result format received when retrieving the number of products impacted by spelling mistake for the attribute "%s"',
                strval($attribute->getCode())
            ));
        }

        return intval($result['count']);
    }

    private function buildSearchQueryStringsForLocalizableAttribute(Attribute $attribute, array $optionCodes, array $localesToImprove): array
    {
        $queriesByLocale = [];
        foreach ($localesToImprove as $locale) {
            foreach (array_chunk($optionCodes, self::OPTIONS_MAX_SIZE) as $optionCodesBulk) {
                $queriesByLocale[] = [
                    'query_string' => [
                        'default_field' => sprintf('values.%s-option*.%s', strval($attribute->getCode()), $locale),
                        'query' => join(' OR ', $optionCodesBulk),
                    ],
                ];
            }
        }

        return $queriesByLocale;
    }

    private function buildSearchQueryStringsForNotLocalizableAttribute(Attribute $attribute, array $optionCodes): array
    {
        $queries = [];
        foreach (array_chunk($optionCodes, self::OPTIONS_MAX_SIZE) as $optionCodesBulk) {
            $queries[] = [
                'query_string' => [
                    'default_field' => sprintf('values.%s-option*', strval($attribute->getCode())),
                    'query' => join(' OR ', $optionCodesBulk),
                ],
            ];
        }

        return $queries;
    }
}
