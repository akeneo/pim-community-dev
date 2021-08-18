<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Webmozart\Assert\Assert;

final class ClientMigration implements ClientMigrationInterface
{
    private Client $client;

    public function __construct(ClientBuilder $clientBuilder, array $hosts)
    {
        $this->client = $clientBuilder->setHosts($hosts)->build();
    }

    public function getIndexNameFromAlias(string $indexAlias): array
    {
        $aliases = $this->client->indices()->getAlias(['name' => $indexAlias]);

        return \array_keys($aliases);
    }

    public function reindex(string $sourceIndexAlias, string $targetIndexAlias, array $query)
    {
        $reindexResponse = $this->client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => $sourceIndexAlias,
                    "query" => $query,
                ],
                "dest" => [
                    "index" => $targetIndexAlias,
                ]
            ]
        ]);

        return $reindexResponse["total"];
    }

    public function removeIndex(string $indexName): void
    {
        $this->assertResponseIsAcknowledged($this->client->indices()->delete(['index' => $indexName]));
    }

    public function getIndexSettings(string $index): array
    {
        $indicesClient = $this->client->indices();
        $settingsResponse = $indicesClient->getSettings(['index' => $index]);

        return $settingsResponse[$index]['settings']['index'];
    }

    public function putIndexSetting(string $indexName, array $indexSettings)
    {
        $indicesClient = $this->client->indices();

        $this->assertResponseIsAcknowledged($indicesClient->putSettings([
            'index' => $indexName,
            'body' => [
                'index' => $indexSettings
            ]
        ]));
    }

    private function assertResponseIsAcknowledged(array $response): void
    {
        Assert::true($response['acknowledged']);
    }

    public function switchIndexAlias(string $oldIndexAlias, string $oldIndexName, string $newIndexAlias, string $newIndexName): void
    {
        $this->assertResponseIsAcknowledged(
            $this->client->indices()->updateAliases([
                'body' => [
                    'actions' => [
                        [
                            'add' => [
                                'alias' => $oldIndexAlias,
                                'index' => $newIndexName,
                            ],
                        ],
                        [
                            'remove' => [
                                'alias' => $oldIndexAlias,
                                'index' => $oldIndexName,
                            ],
                        ],
                        [
                            'add' => [
                                'alias' => $newIndexAlias,
                                'index' => $oldIndexName,
                            ]
                        ],
                        [
                            'remove' => [
                                'alias' => $newIndexAlias,
                                'index' => $newIndexName,
                            ]
                        ],
                    ]
                ]
            ])
        );
    }

    public function createIndex(string $indexName, array $body): void
    {
        $indicesClient = $this->client->indices();

        $this->assertResponseIsAcknowledged(
            $indicesClient->create([
                'index' => $indexName,
                'body' => $body
            ])
        );
    }
}
