<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class FastCleanRemovedAttributesFromProductAndProductModelCommand extends Command
{
    protected static $defaultName = 'pim:product:fast-clean-removed-attribute';

    private GetProductModelIdentifiersWithRemovedAttributeInterface $getProductModelIdentifiersWithRemovedAttribute;
    private GetProductIdentifiersWithRemovedAttributeInterface $getProductIdentifiersWithRemovedAttribute;
    private ProductModelRepositoryInterface $productModelRepository;
    private ProductRepositoryInterface $productRepository;
    private Connection $connection;
    private EventDispatcher $eventDispatcher;
    private UnitOfWorkAndRepositoriesClearer $clearer;

    public function __construct(
        GetProductModelIdentifiersWithRemovedAttributeInterface $getProductModelIdentifiersWithRemovedAttribute,
        GetProductIdentifiersWithRemovedAttributeInterface $getProductIdentifiersWithRemovedAttribute,
        ProductModelRepositoryInterface $productModelRepository,
        ProductRepositoryInterface $productRepository,
        Connection $connection,
        EventDispatcher $eventDispatcher,
        UnitOfWorkAndRepositoriesClearer $clearer
    ) {
        parent::__construct();

        $this->getProductModelIdentifiersWithRemovedAttribute = $getProductModelIdentifiersWithRemovedAttribute;
        $this->getProductIdentifiersWithRemovedAttribute = $getProductIdentifiersWithRemovedAttribute;
        $this->productModelRepository = $productModelRepository;
        $this->productRepository = $productRepository;
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->clearer = $clearer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Removes all values of this attribute on all products and product models')
            ->addOption('batch', null, null, '', 100)
            ->addArgument('attributes', InputArgument::REQUIRED | InputArgument::IS_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int)$input->getOption('batch');
        $attributesCodes = $input->getArgument('attributes');

        foreach ($attributesCodes as $attributeCode) {
            $this->cleanAttributeOnProductModels($attributeCode, $batchSize);
            $this->cleanAttributeOnProducts($attributeCode, $batchSize);
        }

        return 0;
    }

    private function cleanAttributeOnProductModels(string $attributeCode, int $batchSize): void
    {
        $start = microtime(true);
        $batch = 0;
        $count = 0;

        foreach ($this->getProductModelIdentifiersWithRemovedAttribute->nextBatch([$attributeCode],
            $batchSize) as $identifiers) {
            $batch++;
            echo sprintf("memory at %d: %s", $batch, memory_get_usage());

            $count += count($identifiers);
            $productModels = $this->productModelRepository->findBy(['code' => $identifiers]);

            $this->connection->executeQuery(<<<SQL
                UPDATE pim_catalog_product_model
                SET raw_values = JSON_REMOVE(raw_values, :json_path)
                WHERE code IN (:identifiers)
SQL,
                [
                    'json_path' => sprintf('$.%s', $attributeCode),
                    'identifiers' => $identifiers,
                ],
                [
                    'json_path' => Types::STRING,
                    'identifiers' => Connection::PARAM_STR_ARRAY,
                ]);

            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE_ALL,
                new GenericEvent($productModels, [
                    'unitary' => false,
                ])
            );

            $this->clearer->clear();
        }

        $duration = microtime(true) - $start;
        echo sprintf('Cleaned %d product models in %f seconds (%f / pm)', $count, $duration, $duration / $count);
    }

    private function cleanAttributeOnProducts(string $attributeCode, int $batchSize): void
    {
        $start = microtime(true);
        $batch = 0;
        $count = 0;

        foreach ($this->getProductIdentifiersWithRemovedAttribute->nextBatch([$attributeCode],
            $batchSize) as $identifiers) {
            $batch++;
            echo sprintf("memory at %d: %s", $batch, memory_get_usage());

            $count += count($identifiers);
            $products = $this->productRepository->findBy(['identifier' => $identifiers]);

            $this->connection->executeQuery(<<<SQL
                UPDATE pim_catalog_product
                SET raw_values = JSON_REMOVE(raw_values, :json_path)
                WHERE identifier IN (:identifiers)
SQL,
                [
                    'json_path' => sprintf('$.%s', $attributeCode),
                    'identifiers' => $identifiers,
                ],
                [
                    'json_path' => Types::STRING,
                    'identifiers' => Connection::PARAM_STR_ARRAY,
                ]);

            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE_ALL,
                new GenericEvent($products, [
                    'unitary' => false,
                ])
            );

            $this->clearer->clear();
        }

        $duration = microtime(true) - $start;
        echo sprintf('Cleaned %d product in %f seconds (%f / p)', $count, $duration, $duration / $count);
    }
}
