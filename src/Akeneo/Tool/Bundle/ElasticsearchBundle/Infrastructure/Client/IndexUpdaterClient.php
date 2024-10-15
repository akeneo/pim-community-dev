<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client;

use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Monolog\Logger;
use Webmozart\Assert\Assert;

final class IndexUpdaterClient
{
    private NativeClient $client;

    public function __construct(private readonly Logger $logger, NativeClient $client)
    {
        $this->client = $client;
    }

    public function switchIndexAliasToNewIndex(
        string $sourceAliasName,
        string $sourceIndexName,
        string $destinationAliasName,
        string $destinationIndexName
    ) {
        $this->assertResponseIsAcknowledged(
            $this->client->indices()->updateAliases([
                'body' => [
                    'actions' => [
                        [
                            'add' => [
                                'alias' => $sourceAliasName,
                                'index' => $destinationIndexName,
                            ],
                        ],
                        [
                            'remove' => [
                                'alias' => $sourceAliasName,
                                'index' => $sourceIndexName,
                            ],
                        ],
                        [
                            'add' => [
                                'alias' => $destinationAliasName,
                                'index' => $sourceIndexName,
                            ]
                        ],
                        [
                            'remove' => [
                                'alias' => $destinationAliasName,
                                'index' => $destinationIndexName,
                            ]
                        ],
                    ]
                ]
            ])
        );
    }

    public function reindexAllDocuments(string $sourceAliasName, string $destinationAliasName)
    {
        $this->logger->notice("First indexation into the new elasticsearch index");
        $reindexResponse = $this->client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => $sourceAliasName,
                ],
                "dest" => [
                    "index" => $destinationAliasName,
                    "version_type" => "external_gt",
                ]
            ]
        ]);

        $this->logger->notice('Indexation result', ['response' => json_encode($reindexResponse)]);

        $this->logger->notice("Reindex document indexed during first indexation");
        $reindexResponse = $this->client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "conflicts" => "proceed",
                "source" => [
                    "index" => $sourceAliasName,
                ],
                "dest" => [
                    "index" => $destinationAliasName,
                    "version_type" => "external_gt",
                ]
            ]
        ]);

        $this->logger->notice('Indexation result', ['response' => json_encode($reindexResponse)]);
    }

    public function reindexDocumentsAfterSwitch(string $sourceAliasName, string $destinationAliasName)
    {
        $this->logger->notice("Reindex document indexed before the index switch");
        $reindexResponse = $this->client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "conflicts" => "proceed",
                "source" => [
                    "index" => $sourceAliasName,
                ],
                "dest" => [
                    "index" => $destinationAliasName,
                    "version_type" => "external_gt",
                ]
            ]
        ]);

        $this->logger->notice('Indexation result', ['response' => json_encode($reindexResponse)]);
    }

    public function createDestinationIndex(string $destinationIndexName, string $destinationAliasName, array $sourceIndexConfiguration)
    {
        $sourceIndexConfiguration['settings']['index']['number_of_replicas'] = 0;
        $sourceIndexConfiguration['settings']['index']['refresh_interval'] = -1;
        $sourceIndexConfiguration['aliases'] = [$destinationAliasName => (object) []];

        $this->client->indices()->create([
            'index' => $destinationIndexName,
            'body' => $sourceIndexConfiguration,
        ]);
    }

    public function getIndexNameFromAlias(string $aliasName): string
    {
        $aliasConfiguration = $this->client->indices()->get(['index' => $aliasName]);
        $indexNames = array_keys($aliasConfiguration);

        if (count($indexNames) !== 1) {
            throw new \Exception('There is multiple index behind the index alias or there is no alias behind the alias');
        }

        if ($indexNames[0] === $aliasName) {
            throw new \Exception('The index alias have the same index name');
        }

        return $indexNames[0];
    }

    public function getIndexConfiguration(string $aliasToReindex): array
    {
        $aliasConfiguration = $this->client->indices()->get(['index' => $aliasToReindex]);
        $indexName = array_keys($aliasConfiguration)[0];
        $indexConfiguration = $aliasConfiguration[$indexName];
        unset($indexConfiguration['aliases']);
        unset($indexConfiguration['settings']['index']['provided_name']);
        unset($indexConfiguration['settings']['index']['creation_date']);
        unset($indexConfiguration['settings']['index']['uuid']);
        unset($indexConfiguration['settings']['index']['version']);

        return $indexConfiguration;
    }

    public function createAlias(string $aliasName, string $indexName): void
    {
        $this->assertResponseIsAcknowledged(
            $this->client->indices()->updateAliases([
                'body' => [
                    'actions' => [
                        [
                            'add' => [
                                'alias' => $aliasName,
                                'index' => $indexName,
                            ],
                        ],
                    ],
                ]
            ])
        );
    }

    public function resetIndexSettings(string $destinationIndexName, string $sourceIndexName): void
    {
        $sourceIndexSettingsResponse = $this->client->indices()->getSettings(['index' => $sourceIndexName]);
        $sourceIndexSettings = $sourceIndexSettingsResponse[$sourceIndexName]['settings']['index'];
        $this->assertResponseIsAcknowledged(
            $this->client->indices()->putSettings([
                'index' => $destinationIndexName,
                'body' => [
                    'refresh_interval' => $sourceIndexSettings['refresh_interval'] ?? null,
                    'number_of_replicas' => $sourceIndexSettings['number_of_replicas'] ?? 1,
                ]
            ])
        );

        $this->client->indices()->refresh(['index' => $destinationIndexName]);
    }

    public function removeIndex(string $indexName): void
    {
        $this->assertResponseIsAcknowledged($this->client->indices()->delete(['index' => $indexName]));
    }

    public function isAnAlias(string $indexName): bool
    {
        return $this->client->indices()->existsAlias(['name' => $indexName]);
    }

    public function haveAlias(string $indexName): bool
    {
        $aliasConfiguration = $this->client->indices()->get(['index' => $indexName]);

        return array_keys($aliasConfiguration)[0] === $indexName;
    }

    public function renameAlias(string $sourceAliasName, string $sourceIndexName, string $destinationIndexName): void
    {
        $this->assertResponseIsAcknowledged(
            $this->client->indices()->updateAliases([
                'body' => [
                    'actions' => [
                        [
                            'add' => [
                                'alias' => $sourceIndexName,
                                'index' => $destinationIndexName,
                            ],
                        ],
                        [
                            'remove' => [
                                'alias' => $sourceAliasName,
                                'index' => $destinationIndexName,
                            ],
                        ],
                    ]
                ]
            ])
        );
    }

    private function assertResponseIsAcknowledged(array $response): void
    {
        Assert::true($response['acknowledged']);
    }
}
