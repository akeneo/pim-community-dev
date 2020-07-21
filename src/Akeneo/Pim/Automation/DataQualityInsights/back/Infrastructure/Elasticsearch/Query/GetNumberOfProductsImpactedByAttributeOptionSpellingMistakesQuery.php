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
            '_source' => false,
            'size' => 0,
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

        $result = $this->esClient->search($query);

        if (!isset($result['hits']['total']['value'])) {
            throw new \RuntimeException(sprintf(
                'Unexpected result format received when retrieving the number of products impacted by spelling mistake for the attribute "%s"',
                strval($attribute->getCode())
            ));
        }

        return intval($result['hits']['total']['value']);
    }

    private function buildSearchQueryStringsForLocalizableAttribute(Attribute $attribute, array $optionCodes, array $localesToImprove): array
    {
        $queriesByLocale = [];
        foreach ($localesToImprove as $locale) {
            $queriesByLocale[] = [
                'query_string' => [
                    'default_field' => sprintf('values.%s-option*.%s', strval($attribute->getCode()), $locale),
                    'query' => join(' OR ', $optionCodes),
                ],
            ];
        }

        return $queriesByLocale;
    }

    private function buildSearchQueryStringsForNotLocalizableAttribute(Attribute $attribute, array $optionCodes): array
    {
        return [
            'query_string' => [
                'default_field' => sprintf('values.%s-option*', strval($attribute->getCode())),
                'query' => join(' OR ', $optionCodes),
            ],
        ];
    }
}
