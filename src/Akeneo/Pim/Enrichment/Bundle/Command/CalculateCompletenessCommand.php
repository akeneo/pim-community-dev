<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
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

    protected static $defaultName = 'pim:completeness:calculate';

    /** @var Client */
    private $client;

    /** @var int */
    private $batchSize;

    /** @var BulkSaverInterface */
    private $bulkSaver;

    /** @var EntityManagerClearerInterface */
    private $clearer;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    public function __construct(
        Client $client,
        int $batchSize,
        BulkSaverInterface $bulkSaver,
        EntityManagerClearerInterface $clearer,
        ProductQueryBuilderFactoryInterface $pqbFactory
    ) {
        $this->client = $client;
        $this->batchSize = $batchSize;
        $this->bulkSaver = $bulkSaver;
        $this->clearer = $clearer;
        $this->pqbFactory = $pqbFactory;

        parent::__construct(self::$defaultName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Launch the product completeness calculation');
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

        $output->writeln("<info>Generating missing completenesses...</info>");

        $options = [
            'filters' => [
                ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null],
                ['field' => 'family', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ];

        $this->client->refreshIndex();
        $pqb = $this->pqbFactory->create($options);
        $products = $pqb->execute();

        $productsToSave = [];
        foreach ($products as $product) {
            $productsToSave[] = $product;

            if (count($productsToSave) === $this->batchSize) {
                $this->bulkSaver->saveAll($productsToSave);
                $this->clearer->clear();
                $productsToSave = [];
            }
        }

        if (!empty($productsToSave)) {
            $this->bulkSaver->saveAll($productsToSave);
        }

        $output->writeln("<info>Missing completenesses generated.</info>");
    }
}
