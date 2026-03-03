<?php

namespace Akeneo\Platform\Bundle\FrameworkBundle\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Requirements\Requirement;
use Symfony\Requirements\RequirementCollection;

class CheckUpdateRequirementsCommand extends Command
{
    protected static $defaultName = 'pim:update:check-requirements';
    private Client $client;

    public function __construct(
        private ClientRegistry $clientRegistry,
        ClientBuilder $clientBuilder,
        private array $elasticsearchHosts
    ) {
        parent::__construct();

        $this->client = $clientBuilder->setHosts($elasticsearchHosts)->build();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $requirements = new RequirementCollection();

        $this->validateThatElasticsearchIndexesAreCompliant($requirements);
        $this->renderRequirements($requirements, $output);

        if (count($requirements->getFailedRequirements())) {
            $this->renderFailedRequirements($requirements, $output);

            $output->writeln('<error>Some update requirements are not fulfilled. Please check output messages and fix them.</error>');

            return self::FAILURE;
        }

        $output->writeln('<info>Update requirements are fulfilled</info>');

        return self::SUCCESS;
    }

    private function validateThatElasticsearchIndexesAreCompliant(RequirementCollection $requirements): void
    {
        $firstElasticsearchHost = current($this->elasticsearchHosts);
        $registeredAlias = $this->getAliasUsedByThePIM();

        $indexConfigurations = $this->client->indices()->get(['index' => '*']);
        foreach ($indexConfigurations as $indexName => $indexConfiguration) {
            $aliasName = $indexName;
            if (!empty($indexConfiguration['aliases'])) {
                $aliasName = array_keys($indexConfiguration['aliases'])[0];
            }

            $versionCreated = $indexConfiguration['settings']['index']["version"]["created"];
            $requirements->add(
                new Requirement(
                    str_starts_with($versionCreated, '7') || str_starts_with($versionCreated, '8'),
                    "Index $indexName creation version",
                    !in_array($aliasName, $registeredAlias) ?
                        "The index $indexName seems to not be used by the PIM, please check if you use it. If you didn't use it delete it: curl --location --request DELETE 'http://$firstElasticsearchHost/$indexName'. If you want to keep it, reindex it with ElasticSearch 7: bin/console akeneo:elasticsearch:update-index-version $aliasName"
                        : "The index $indexName should be re-indexed in order to be created with Elasticsearch 7, run: bin/console akeneo:elasticsearch:update-index-version $aliasName"
                )
            );
        }
    }

    private function getAliasUsedByThePIM(): array
    {
        $registeredIndexNames = [];
        $clients = $this->clientRegistry->getClients();
        foreach ($clients as $client) {
            $registeredIndexNames[] = $client->getIndexName();
        }

        return $registeredIndexNames;
    }

    private function renderRequirements(RequirementCollection $requirements, OutputInterface $output): void
    {
        $table = new Table($output);

        $table->setHeaders(['Check', 'Status']);
        foreach ($requirements->all() as $requirement) {
            if ($requirement->isFulfilled()) {
                $table->addRow([$requirement->getTestMessage(), 'OK']);
                continue;
            }

            $table->addRow([$requirement->getTestMessage(), 'ERROR']);
        }

        $table->render();
    }

    private function renderFailedRequirements(RequirementCollection $requirements, OutputInterface $output): void
    {
        $table = new Table($output);

        $table->setHeaders(['Recommendation']);
        foreach ($requirements->getFailedRequirements() as $failedRequirement) {
            $table->addRow([$failedRequirement->getHelpText()]);
        }

        $table->render();
    }
}
