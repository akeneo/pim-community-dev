<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Calculate the completeness of the products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateCompletenessCommand extends Command
{
    use LockableTrait;

    private const DEFAULT_BATCH_SIZE = 1000;

    protected static $defaultName = 'pim:completeness:calculate';

    public function __construct(
        private readonly ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        private readonly ComputeAndPersistProductCompletenesses $computeAndPersistProductCompleteness,
        private readonly Connection $connection
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Launch the product completeness calculation')
            ->addOption(
                'batch-size',
                null,
                InputArgument::OPTIONAL,
                'The number of product completeness calculated in one cycle.',
                self::DEFAULT_BATCH_SIZE
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln(sprintf('The command "%s" is still running in another process.', self::$defaultName));

            return 0;
        }

        $batchSize = (int) $input->getOption('batch-size') ?: self::DEFAULT_BATCH_SIZE;

        $progressBar = new ProgressBar($output, $this->getTotalNumberOfProducts());

        $output->writeln('<info>Computing product completenesses...</info>');
        $progressBar->start();
        foreach ($this->getProductUuids($batchSize) as $productUuids) {
            $this->computeAndPersistProductCompleteness->fromProductUuids($productUuids);
            $this->productAndAncestorsIndexer->indexFromProductUuids($productUuids);
            $progressBar->advance(count($productUuids));
        }
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Completeness successfully computed.</info>');

        return 0;
    }

    private function getTotalNumberOfProducts(): int
    {
        return $this->connection->executeQuery('SELECT COUNT(0) FROM pim_catalog_product')->fetchOne();
    }

    /**
     * @return iterable<UuidInterface>
     */
    private function getProductUuids(int $batchSize): iterable
    {
        $lastUuidAsBytes = '';
        $sql = <<<SQL
SELECT uuid
FROM pim_catalog_product
WHERE uuid > :lastUuid
ORDER BY uuid ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->fetchFirstColumn(
                $sql,
                [
                    'lastUuid' => $lastUuidAsBytes,
                    'limit' => $batchSize,
                ],
                [
                    'lastUuid' => \PDO::PARAM_STR,
                    'limit' => \PDO::PARAM_INT,
                ]
            );

            if (empty($rows)) {
                return;
            }

            $lastUuidAsBytes = end($rows);

            yield array_map(fn (string $uuidAsBytes): UuidInterface => Uuid::fromBytes($uuidAsBytes), $rows);
        }
    }
}
