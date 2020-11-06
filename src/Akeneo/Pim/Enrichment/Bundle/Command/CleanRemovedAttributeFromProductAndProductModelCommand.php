<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\SharedCatalog\Query\GetProductIdFromProductIdentifierQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class CleanRemovedAttributeFromProductAndProductModelCommand extends Command
{
    const BATCH_SIZE = 100;

    protected static $defaultName = 'pim:product:clean-removed-attribute:v2';

    private ProductQueryBuilderFactoryInterface $productIdentifierQueryBuilderFactory;
    private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory;
    private GetProductIdFromProductIdentifierQueryInterface $getProductIdFromProductIdentifierQuery;
    private Connection $connection;
    private ProductIndexer $productIndexer;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        ProductQueryBuilderFactoryInterface $productIdentifierQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        GetProductIdFromProductIdentifierQueryInterface $getProductIdFromProductIdentifierQuery,
        Connection $connection,
        ProductIndexer $productIndexer,
        EventDispatcher $eventDispatcher
    ) {
        parent::__construct();

        $this->productIdentifierQueryBuilderFactory = $productIdentifierQueryBuilderFactory;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->getProductIdFromProductIdentifierQuery = $getProductIdFromProductIdentifierQuery;
        $this->connection = $connection;
        $this->productIndexer = $productIndexer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Removes all values of this attribute on all products and product models')
            ->addArgument('code', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $attributeCode = $input->getArgument('code');

//        $this->cleanAttributeOnProductsIdentifiers($attributeCode, $output);
        $this->cleanAttributeOnProducts($attributeCode, $output);

        return 0;
    }

//    private function cleanAttributeOnProductsIdentifiers(string $attributeCode, OutputInterface $output): void
//    {
//        $searchAfterProductIdentifier = null;
//
//        while (true) {
//            $productIdentifiers = $this->batchProductsIdentifiers($attributeCode, $searchAfterProductIdentifier);
//            if (count($productIdentifiers) === 0) {
//                break;
//            }
//
//            $searchAfterProductIdentifier = count($productIdentifiers) > 0 ? $productIdentifiers[count($productIdentifiers) - 1] : null;
//
//            $output->writeln(sprintf('update raw_values of %d products', count($productIdentifiers)));
//
//            $this->connection->executeQuery(<<<SQL
//                UPDATE pim_catalog_product
//                SET raw_values = JSON_REMOVE(raw_values, :json_path)
//                WHERE identifier IN (:identifiers)
//SQL,
//                [
//                    'json_path' => sprintf('$.%s', $attributeCode),
//                    'identifiers' => $productIdentifiers,
//                ],
//                [
//                    'json_path' => Types::STRING,
//                    'identifiers' => Connection::PARAM_STR_ARRAY,
//                ]);
//            $this->productIndexer->indexFromProductIdentifiers($productIdentifiers);
//        };
//    }
//
//    private function batchProductsIdentifiers(
//        string $attributeCode,
//        ?string $searchAfterProductIdentifier = null
//    ): array {
//        $pqbOptions = [
//            'filters' => [
//                [
//                    'field' => $attributeCode,
//                    'operator' => Operators::IS_NOT_EMPTY,
//                    'value' => '',
//                ],
//            ],
//            'limit' => self::BATCH_SIZE,
//        ];
//
//        if (null !== $searchAfterProductIdentifier) {
//            $searchAfterProductId = $this->getProductIdFromProductIdentifierQuery->execute($searchAfterProductIdentifier);
//
//            if (null === $searchAfterProductId) {
//                throw new \InvalidArgumentException(sprintf(
//                    'Product with identifier "%s" not found',
//                    $searchAfterProductIdentifier
//                ));
//            }
//
//            $pqbOptions['search_after'] = [
//                strtolower($searchAfterProductIdentifier),
//                'product_' . $searchAfterProductId,
//            ];
//        }
//
//        $pqb = $this->productIdentifierQueryBuilderFactory->create($pqbOptions);
//        $pqb->addSorter('identifier', Directions::ASCENDING);
//
//        $results = $pqb->execute();
//
//        return array_map(function (IdentifierResult $result) {
//            return $result->getIdentifier();
//        }, iterator_to_array($results));
//    }

    private function cleanAttributeOnProducts(string $attributeCode, OutputInterface $output): void
    {
        $searchAfterProductIdentifier = null;

        while (true) {
            $products = $this->batchProducts($attributeCode, $searchAfterProductIdentifier);
            $productIdentifiers = array_map(function (ProductInterface $product) {
                return $product->getIdentifier();
            }, iterator_to_array($products));

            if (count($productIdentifiers) === 0) {
                break;
            }

            $searchAfterProductIdentifier = count($productIdentifiers) > 0 ? $productIdentifiers[count($productIdentifiers) - 1] : null;

            $output->writeln(sprintf('update raw_values of %d products', count($productIdentifiers)));

            $this->connection->executeQuery(<<<SQL
                UPDATE pim_catalog_product
                SET raw_values = JSON_REMOVE(raw_values, :json_path)
                WHERE identifier IN (:identifiers)
SQL,
                [
                    'json_path' => sprintf('$.%s', $attributeCode),
                    'identifiers' => $productIdentifiers,
                ],
                [
                    'json_path' => Types::STRING,
                    'identifiers' => Connection::PARAM_STR_ARRAY,
                ]);

            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE_ALL,
                new GenericEvent(iterator_to_array($products), [
                    'unitary' => false,
                ])
            );
        };
    }

    /**
     * @return CursorInterface|ProductInterface[]
     */
    private function batchProducts(
        string $attributeCode,
        ?string $searchAfterProductIdentifier = null
    ): CursorInterface {
        $pqbOptions = [
            'filters' => [
                [
                    'field' => $attributeCode,
                    'operator' => Operators::IS_NOT_EMPTY,
                    'value' => '',
                ],
            ],
            'limit' => self::BATCH_SIZE,
        ];

        if (null !== $searchAfterProductIdentifier) {
            $searchAfterProductId = $this->getProductIdFromProductIdentifierQuery->execute($searchAfterProductIdentifier);

            if (null === $searchAfterProductId) {
                throw new \InvalidArgumentException(sprintf(
                    'Product with identifier "%s" not found',
                    $searchAfterProductIdentifier
                ));
            }

            $pqbOptions['search_after'] = [
                strtolower($searchAfterProductIdentifier),
                'product_' . $searchAfterProductId,
            ];
        }

        $pqb = $this->productQueryBuilderFactory->create($pqbOptions);
        $pqb->addSorter('identifier', Directions::ASCENDING);

        return $pqb->execute();
    }
}
