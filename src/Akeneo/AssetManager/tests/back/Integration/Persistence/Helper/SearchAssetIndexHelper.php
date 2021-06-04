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

namespace Akeneo\AssetManager\Integration\Persistence\Helper;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

/**
 * This class is responsible for helping in the elasticsearch index setup in tests.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchAssetIndexHelper
{
    private Client $assetClient;

    public function __construct(Client $assetClient)
    {
        $this->assetClient = $assetClient;
    }

    public function resetIndex(): void
    {
        $this->assetClient->resetIndex();
    }

    public function index(array $assets): void
    {
        foreach ($assets as $asset) {
            if (!array_key_exists('identifier', $asset)) {
                throw new \InvalidArgumentException('Expect to index asset with a "identifier" property. None found.');
            }

            $this->assetClient->index($asset['identifier'], $asset);
        }

        $this->assetClient->refreshIndex();
    }

    public function search(string $assetFamilyCode, string $channel, string $locale, array $terms): array
    {
        $this->refreshIndex();

        $query = $this->getQuery($assetFamilyCode, $channel, $locale, $terms);

        return $this->executeQuery($query);
    }

    public function findAssetsByAssetFamily(string $assetFamilyCode): array
    {
        $this->refreshIndex();

        $query = [
            '_source' => '_id',
            'query' => [
                'match' => ['asset_family_code' => $assetFamilyCode,],
            ],
        ];

        return $this->executeQuery($query);
    }

    public function findAsset(string $assetFamilyCode, string $assetCode): array
    {
        $this->refreshIndex();

        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['asset_family_code' => $assetFamilyCode]],
                        ['term' => ['code' => $assetCode]],
                    ],
                ],
            ],
        ];

        return $this->executeQuery($query);
    }

    public function assertAssetExists(string $assetFamilyCode, string $assetCode): void
    {
        $matchingIdentifiers = $this->findAsset($assetFamilyCode, $assetCode);

        Assert::assertCount(1, $matchingIdentifiers, sprintf('Asset not found: %s_%s', $assetFamilyCode, $assetCode));
    }

    public function assertAssetDoesNotExists(string $assetFamilyCode, string $assetCode): void
    {
        $matchingIdentifiers = $this->findAsset($assetFamilyCode, $assetCode);

        Assert::assertCount(0, $matchingIdentifiers, sprintf('This asset should not exist: %s_%s', $assetFamilyCode, $assetCode));
    }

    public function executeQuery(array $query): array
    {
        $matches = $this->assetClient->search($query);
        $documents = $matches['hits']['hits'] ?? [];

        $matchingIdentifiers = [];
        foreach ($documents as $document) {
            $matchingIdentifiers[] = $document['_id'];
        }

        return $matchingIdentifiers;
    }

    public function refreshIndex()
    {
        $this->assetClient->refreshIndex();
    }

    private function getQuery(string $assetFamilyCode, $channel, $locale, array $terms): array
    {
        $query = [
            '_source' => '_id',
            'sort' => ['updated_at' => 'desc'],
            'query' => [
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
        ];

        foreach ($terms as $term) {
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'default_field' => sprintf('asset_full_text_search.%s.%s', $channel, $locale),
                    'query' => sprintf('*%s*', $term),
                ],
            ];
        }

        return $query;
    }
}
