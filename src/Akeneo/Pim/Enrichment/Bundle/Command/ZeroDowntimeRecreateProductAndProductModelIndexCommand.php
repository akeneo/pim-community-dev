<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWithoutDowntime;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @copyright 2024 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ZeroDowntimeRecreateProductAndProductModelIndexCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 1000;

    protected static $defaultName = 'akeneo:pim:zero-downtime-recreate-product-and-product-model-index';
    protected static $defaultDescription = <<<EOL
        Zero-downtime reindexing of product and product models. 
        
        Reindex all documents in the product and product model index using Elasticsearch's Reindex API.
        This allows removing "ghost" fields from the new index and prevents the index from reaching the
        `index.mapping.total_fields.limit` or `index.mapping.nested_fields.limit` limits
        EOL;

    public function __construct(
        private readonly Client $client,
        private readonly UpdateIndexMappingWithoutDowntime $updateIndexMappingWithoutDowntime,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of products to index per batch',
                self::DEFAULT_BATCH_SIZE
            );
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->isInteractive()) {
            $io = new SymfonyStyle($input, $output);
            $io->warning(
                <<<EOL
            This command will recreate the current product and product model index, and will copy every document as is.
            It is only useful to reset the index's mapping configuration (fields limit) and should not be used to
            refresh the content of the documents.
            EOL
            );
            if (!$io->confirm('Do you want to continue?', false)) {
                $output->writeln('<info>Product and product model reindexing cancelled</info>');

                return Command::SUCCESS;
            }
        }

        $batchSize = (int) $input->getOption('batch-size') ?: self::DEFAULT_BATCH_SIZE;

        $indexConfiguration = $this->client->getConfigurationLoader()->load();
        $currentIndexAlias = $this->client->getIndexName();
        $newIndexName = \sprintf('%s_%s', $currentIndexAlias, Uuid::uuid4()->toString());
        $temporaryAliasName = \sprintf('%s_alias', $newIndexName);

        $this->logger->notice(
            'Recreating product and product model index',
            [
                'source_alias' => $currentIndexAlias,
                'target_temporary_alias' => $temporaryAliasName,
                'target_index_name' => $newIndexName,
            ]
        );

        $this->updateIndexMappingWithoutDowntime->execute(
            sourceIndexAlias: $currentIndexAlias,
            destinationIndexAlias: $temporaryAliasName,
            destinationIndexName: $newIndexName,
            indexConfiguration: $indexConfiguration,
            findUpdatedDocumentQuery: static fn (\DateTimeImmutable $referenceDatetime): array => [
                'range' => [
                    'updated' => ['gt' => $referenceDatetime->format('c')]
                ],
            ],
            batchSize: $batchSize,
        );

        $this->logger->notice('Operation successful');

        return Command::SUCCESS;
    }
}
