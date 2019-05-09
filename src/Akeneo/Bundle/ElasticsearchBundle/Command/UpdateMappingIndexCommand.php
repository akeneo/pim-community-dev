<?php

declare(strict_types=1);

namespace Akeneo\Bundle\ElasticsearchBundle\Command;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Bundle\ElasticsearchBundle\UpdateIndexMapping;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update the mapping of an index without needing to reindex everything 1 by 1
 * This can be use during an upgrade of ES or a change on the mapping
 *
 * @author    Anael Chardan <anael.chardan@gmail.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateMappingIndexCommand extends Command
{
    protected static $defaultName = 'akeneo:elasticsearch:update-mapping';

    /** @var ClientRegistry */
    private $esClientsRegistry;

    /** @var array */
    private $hosts;

    public function __construct(ClientRegistry $clientRegistry, $hosts)
    {
        $this->esClientsRegistry = $clientRegistry;
        $this->hosts = is_string($hosts) ? [$hosts] : $hosts;
        parent::__construct(self::$defaultName);
    }

    public function configure()
    {
        $this
            ->addArgument(
            'indices',
            InputArgument::IS_ARRAY,
            'Elasticsearch indices name to reindex, separated by spaces'
        );

        $this->addOption('all', 'a', InputOption::VALUE_NONE, "Use --all if you want to update all mappings of all indices", null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $indices = $input->getOption('all') ? [] : $input->getArgument('indices');
        $clients = $this->esClients($indices);
        $updateIndexMapping = new UpdateIndexMapping();
        foreach ($clients as $client) {
            $nativeClient = $this->buildNativeClient($client);
            $updateIndexMapping->updateIndexMapping($nativeClient['client'], $nativeClient['name'], $nativeClient['configuration']);
        }
    }

    private function buildNativeClient(Client $client): array
    {
        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts($this->hosts);
        $nativeClient = $clientBuilder->build();

        return [
            'client' => $nativeClient,
            'configuration' => $client->getConfigurationLoader(),
            'name' => $client->getIndexName()
        ];
    }


    private function esClients(array $indexNames = []): array
    {
        return array_filter($this->esClientsRegistry->getClients(), function (Client $client) use ($indexNames) {
            if ($indexNames === []) {
                return $client;
            }

            return in_array($client->getIndexName(), $indexNames);
        });
    }
}
