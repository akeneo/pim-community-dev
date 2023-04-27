<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetDeletedProductDocumentIds;
use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetDeletedProductModelDocumentIds;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Erases documents present in Elasticsearch but not present in MySQL
 *
 * @author    Anne-Laure Jouhanneau <anne-laure.jouhanneau@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanRemovedProductsCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 100;

    protected static $defaultName = 'pim:product:clean-removed-products';
    protected static $defaultDescription = 'Erase documents present in Elasticsearch but missing in MySQL';

    public function __construct(
        private readonly GetDeletedProductDocumentIds $getDeletedProductDocumentIds,
        private readonly GetDeletedProductModelDocumentIds $getDeletedProductModelDocumentIds,
        private readonly Client $productAndProductModelClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkIndexExists();

        $deletedProductModelCount = $this->deleteDocumentsFromIndex(($this->getDeletedProductModelDocumentIds)(), $output);
        $output->writeln('');
        $output->writeln(\sprintf('<info>%d product model documents removed from the index</info>', $deletedProductModelCount));

        $deletedProductCount = $this->deleteDocumentsFromIndex(($this->getDeletedProductDocumentIds)(), $output);
        $output->writeln('');
        $output->writeln(\sprintf('<info>%d product documents removed from the index</info>', $deletedProductCount));

        return Command::SUCCESS;
    }

    private function deleteDocumentsFromIndex(iterable $getDeletedDocumentIds, OutputInterface $output): int
    {
        $numberOfDeleteDocuments = 0;
        $documentIdsToRemove = [];

        $progressBar = new ProgressBar($output, 0);
        $progressBar->start();

        foreach ($getDeletedDocumentIds as $id) {
            $documentIdsToRemove[] = $id;
            if (\count($documentIdsToRemove) >= self::DEFAULT_BATCH_SIZE) {
                $this->productAndProductModelClient->bulkDelete($documentIdsToRemove);
                $numberOfDeleteDocuments += \count($documentIdsToRemove);
                $progressBar->advance(\count($documentIdsToRemove));
                $documentIdsToRemove = [];
            }
        }

        $progressBar->finish();

        if (\count($documentIdsToRemove) > 0) {
            $this->productAndProductModelClient->bulkDelete($documentIdsToRemove);
            $numberOfDeleteDocuments += \count($documentIdsToRemove);
        }

        return $numberOfDeleteDocuments;
    }

    private function checkIndexExists(): void
    {
        if (!$this->productAndProductModelClient->hasIndex()) {
            throw new \RuntimeException(
                sprintf(
                    'The index "%s" does not exist in Elasticsearch.',
                    $this->productAndProductModelClient->getIndexName()
                )
            );
        }
    }
}
