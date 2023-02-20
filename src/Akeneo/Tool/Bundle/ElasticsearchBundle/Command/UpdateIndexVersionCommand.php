<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMapping;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client\ClientMigration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client\ClientMigrationInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client\IndexUpdaterClient;
use Elasticsearch\ClientBuilder;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateIndexVersionCommand extends Command
{
    protected static $defaultName = 'akeneo:elasticsearch:update-index-version';

    public function __construct(private IndexUpdaterClient $indexUpdaterClient)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->addArgument(
                'indices',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Elasticsearch indices name to update, separated by spaces'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceAliasNames = $input->getArgument('indices');
        foreach ($sourceAliasNames as $sourceAliasName) {
            $destinationAliasName = sprintf('%s_alias_%s', $sourceAliasName, Uuid::uuid4());
            $destinationIndexName = sprintf('%s_%s', $sourceAliasName, Uuid::uuid4());
            $output->writeln("<info>Recapitulation of the operation:
    - Source alias: $sourceAliasName
    - Destination alias: $destinationAliasName
    - Destination index: $destinationIndexName
    </info>");

            $question = new ConfirmationQuestion("<question>Are you sure to update $sourceAliasName index? [Y/n]</question>", true);
            if (!$this->getHelper('question')->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }

            $this->updateIndexWithoutDowntime(
                $sourceAliasName,
                $destinationAliasName,
                $destinationIndexName
            );

            $output->writeln("<info>Index $sourceAliasName have been updated</info>");
        }

        return Command::SUCCESS;
    }

    function updateIndexWithoutDowntime(
        string $sourceAliasName,
        string $destinationAliasName,
        string $destinationIndexName,
    ): void {
        $sourceIndexConfiguration = $this->indexUpdaterClient->getIndexConfiguration($sourceAliasName);
        $sourceIndexHaveAlias = $this->indexUpdaterClient->indexHaveAlias($sourceAliasName);
        if (!$sourceIndexHaveAlias) {
            $newSourceAliasName = sprintf('%s_migration_alias', $sourceAliasName);
            $this->indexUpdaterClient->createAlias($newSourceAliasName, $sourceAliasName);
            $sourceAliasName = $newSourceAliasName;
        }

        $sourceIndexName = $this->indexUpdaterClient->getIndexNameFromAlias($sourceAliasName);
        $this->indexUpdaterClient->createDestinationIndex($destinationIndexName, $destinationAliasName, $sourceIndexConfiguration);
        $this->indexUpdaterClient->reindexAllDocuments($sourceAliasName, $destinationAliasName);

        $this->indexUpdaterClient->resetIndexSettings($destinationIndexName, $sourceIndexName);
        $this->indexUpdaterClient->switchIndexAliasToNewIndex(
            $sourceAliasName,
            $sourceIndexName,
            $destinationAliasName,
            $destinationIndexName
        );

        $this->indexUpdaterClient->reindexDocumentsAfterSwitch($destinationAliasName, $sourceAliasName);
        $this->indexUpdaterClient->removeIndex($sourceIndexName);
        if (!$sourceIndexHaveAlias) {
            $this->indexUpdaterClient->renameAlias($sourceAliasName, $sourceIndexName, $destinationIndexName);
        }
    }
}
