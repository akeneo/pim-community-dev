<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
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

    private const BATCH_SIZE = 1000;

    protected static $defaultName = 'pim:completeness:calculate';

    /** @var ProductAndAncestorsIndexer */
    private $productAndAncestorsIndexer;

    /** @var ComputeAndPersistProductCompletenesses */
    private $computeAndPersistProductCompleteness;

    /** @var Connection */
    private $connection;

    public function __construct(
        ProductAndAncestorsIndexer $productANdAncestorsIndexer,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompleteness,
        Connection $connection
    ) {
        parent::__construct();
        $this->productAndAncestorsIndexer = $productANdAncestorsIndexer;
        $this->computeAndPersistProductCompleteness = $computeAndPersistProductCompleteness;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Launch the product completeness calculation');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln(sprintf('The command "%s" is still running in another process.', self::$defaultName));

            return 0;
        }

        $progressBar = new ProgressBar($output, $this->getTotalNumberOfProducts());

        $output->writeln('<info>Computing product completenesses...</info>');
        $progressBar->start();
        foreach ($this->getProductIdentifiers() as $productIdentifiers) {
            $this->computeAndPersistProductCompleteness->fromProductIdentifiers($productIdentifiers);
            $this->productAndAncestorsIndexer->indexFromProductIdentifiers($productIdentifiers);
            $progressBar->advance(count($productIdentifiers));
        }
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Completeness successfully computed.</info>');
    }

    private function getTotalNumberOfProducts(): int
    {
        return $this->connection->executeQuery('SELECT COUNT(0) FROM pim_catalog_product')->fetchColumn();
    }

    private function getProductIdentifiers(): iterable
    {
        $formerId = 0;
        $sql = <<<SQL
SELECT id, identifier
FROM pim_catalog_product
WHERE id > :formerId
ORDER BY id ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'formerId' => $formerId,
                    'limit' => self::BATCH_SIZE,
                ],
                [
                    'formerId' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchAll();

            if (empty($rows)) {
                return;
            }

            $formerId = end($rows)['id'];
            yield array_column($rows, 'identifier');
        }
    }
}
