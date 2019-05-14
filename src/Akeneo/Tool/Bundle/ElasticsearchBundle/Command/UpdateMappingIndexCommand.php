<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMapping;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Update the mapping of an index without needing to reindex everything 1 by 1
 * This can be used during an upgrade of ES or a change on the mapping
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

        $io = new SymfonyStyle($input, $output);

        $warning = <<<TXT
This command will move your elasticsearch indices to new indices to take into account the new mapping.
The move is done through an aliasing strategy in order to make it as fast as possible.
If you use snapshot/restore feature for your indices, be sure that they are compatible to be restored even with an aliasing strategy.
Do not execute this directly in production and while the prod is alive.
TXT;

        $io->warning($warning);

        if (!$io->confirm('Are you sure to continue?', true)) {
            $output->writeln("<info>You decided to abort your Elasticearch mapping update</info>");

            return;
        }

        $clients = $this->esClients($indices);
        $names = array_map(function (Client $client): string {
            return $client->getIndexName();
        }, $clients);
        $io->writeln("You will migrate those indices (if it misses one you gave, it means that you didn't write it correctly) : ");
        $io->listing($names);

        $updateIndexMapping = new UpdateIndexMapping();
        foreach ($clients as $client) {
            $io->note("Starting to migrate: {$client->getIndexName()}");
            $nativeClient = $this->buildNativeClient($client);
            $updateIndexMapping->updateIndexMapping($nativeClient['client'], $nativeClient['name'], $nativeClient['configuration']);
            $io->note("Finished to migrate: {$client->getIndexName()}");
        }

        $io->success("All the indices listed above have been migrated");
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
                return true;
            }

            return in_array($client->getIndexName(), $indexNames);
        });
    }
}
