<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class CalculateCompletenessCommand extends ContainerAwareCommand
{
    use LockableTrait;

    protected static $defaultName = 'pim:completeness:calculate';

    /** @var Client */
    private $productAndProductModelClient;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var BulkSaverInterface */
    private $productSaver;

    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    /** @var int */
    private $batchSize;

    public function __construct(
        Client $productAndProductModelClient,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $productSaver,
        EntityManagerClearerInterface $cacheClearer,
        int $batchSize
    ) {
        parent::__construct();
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productSaver = $productSaver;
        $this->cacheClearer = $cacheClearer;
        $this->batchSize = $batchSize;
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

        $output->writeln("<info>Generating missing completenesses...</info>");

        $options = [
            'filters' => [
                ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null],
                ['field' => 'family', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ];

        $this->productAndProductModelClient->refreshIndex();

        $pqb = $this->productQueryBuilderFactory->create($options);
        $products = $pqb->execute();

        $productsToSave = [];
        foreach ($products as $product) {
            $productsToSave[] = $product;

            if (count($productsToSave) === $this->batchSize) {
                $this->productSaver->saveAll($productsToSave);
                $this->cacheClearer->clear();

                $productsToSave = [];
            }
        }

        if (!empty($productsToSave)) {
            $this->productSaver->saveAll($productsToSave);
        }

        $output->writeln("<info>Missing completenesses generated.</info>");
    }
}
