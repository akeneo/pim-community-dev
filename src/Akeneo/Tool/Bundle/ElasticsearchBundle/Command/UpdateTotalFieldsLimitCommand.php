<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Tool\Bundle\ElasticsearchBundle\GetTotalFieldsLimit;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\IndicesNamespace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTotalFieldsLimitCommand extends Command
{
    protected static $defaultName = 'akeneo:elasticsearch:update-total-fields-limit';

    private ClientRegistry $esClientsRegistry;

    private GetTotalFieldsLimit $getTotalFieldsLimit;

    private array $hosts;

    private array $indexesToUpdate;

    public function __construct(
        ClientRegistry $esClientsRegistry,
        GetTotalFieldsLimit $getTotalFieldsLimit,
        $hosts,
        array $indexesToUpdate
    ) {
        parent::__construct();

        $this->esClientsRegistry = $esClientsRegistry;
        $this->getTotalFieldsLimit = $getTotalFieldsLimit;
        $this->hosts = is_string($hosts) ? [$hosts] : $hosts;
        $this->indexesToUpdate = $indexesToUpdate;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $newIndexLimit = $this->getTotalFieldsLimit->getLimit();
        if ($newIndexLimit === 0) {
            return;
        }

        foreach ($this->getEsClients() as $client) {
            $nativeClient = $this->buildNativeClient($client);

            $currentIndexLimit = $this->getIndexCurrentTotalFieldsLimit($nativeClient['client'], $nativeClient['indexName']);

            if ($currentIndexLimit !== $newIndexLimit) {
                $output->writeln(sprintf(
                    'Update total fields limit of index %s from %d to %d',
                    $nativeClient['indexName'],
                    $currentIndexLimit,
                    $newIndexLimit
                ));
                $this->updateIndexTotalFieldsLimit($nativeClient['client'], $nativeClient['indexName'], $newIndexLimit);
            }
        }
    }

    private function getIndexCurrentTotalFieldsLimit(\Elasticsearch\Client $client, string $indexName): int
    {
        $indices = $client->indices();
        $indexSettingsWithAlias = $indices->getSettings(['index' => $indexName]);
        $indexSettings = array_shift($indexSettingsWithAlias)['settings'];

        return (int) $indexSettings['index']['mapping']['total_fields']['limit'];
    }

    private function updateIndexTotalFieldsLimit(\Elasticsearch\Client $client, string $indexName, int $newLimit): void
    {
        $indices = $client->indices();
        $indices->putSettings([
            'index' => $indexName,
            'body' => [
                'index' => [
                    'mapping' => [
                        'total_fields' => [
                            'limit' => $newLimit,
                        ]
                    ]
                ]
            ],
        ]);
    }

    private function buildNativeClient(Client $client): array
    {
        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts($this->hosts);
        $nativeClient = $clientBuilder->build();

        return [
            'client' => $nativeClient,
            'indexName' => $client->getIndexName()
        ];
    }

    private function getEsClients(): array
    {
        return array_filter($this->esClientsRegistry->getClients(), fn (Client $client) => in_array($client->getIndexName(), $this->indexesToUpdate));
    }
}
