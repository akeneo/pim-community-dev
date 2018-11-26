<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruAttributeRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class TestCacheLruCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('test:cache:lru');
    }

    /**
     * - Load products by batch  of X for the whole catalog
     * - Then find attributes
     * - Then clear the UOW
     *
     *
     * Foal is to imitate the loading of the attributes during a job, and comparing it to a cache LRU.
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = 30000;
        $cacheSize = 500;

        //$this->doctrineWithoutCache($batchSize);
        $this->doctrineWithCache($batchSize);
        //$this->sqlWithoutCache($batchSize);
        $this->sqlWithLruCache($batchSize, $cacheSize);
        $this->sqlWithArrayLruCache($batchSize, $cacheSize);
        //$this->sqlWithoutCacheBatchCall($batchSize);
        $this->sqlWithLruCacheBatchCall($batchSize, $cacheSize);
        $this->sqlWithArrayLruCacheBatchCall($batchSize, $cacheSize);
    }

    private function doctrineWithoutCache(int $batchSize)
    {
        $repository =  $this->getContainer()->get('pim_catalog.repository.attribute');
        $elapsedTime = $this->benchmark($batchSize, $repository);
        echo "Doctrine without cache $elapsedTime" . PHP_EOL;
    }

    private function doctrineWithCache(int $batchSize)
    {
        $repository =  $this->getContainer()->get('pim_catalog.repository.cached_attribute');
        $elapsedTime = $this->benchmark($batchSize, $repository);
        echo "Doctrine with cache $elapsedTime" . PHP_EOL;
    }

    private function sqlWithoutCache(int $batchSize)
    {
        $repository =  $this->getContainer()->get('pim_catalog.repository.sql_attribute');
        $elapsedTime = $this->benchmark($batchSize, $repository);
        echo "Sql without cache $elapsedTime" . PHP_EOL;
    }

    private function sqlWithLruCache(int $batchSize, int $cacheSize)
    {
        $repository =  new LruArrayAttributeRepository($this->getContainer()->get('pim_catalog.repository.sql_attribute'), $cacheSize);
        $elapsedTime = $this->benchmark($batchSize, $repository);
        echo "Sql with LRU cache $elapsedTime" . PHP_EOL;
    }

    private function sqlWithArrayLruCache(int $batchSize, int $cacheSize)
    {
        $repository =  new LruArrayAttributeRepository($this->getContainer()->get('pim_catalog.repository.sql_attribute'), $cacheSize);
        $elapsedTime = $this->benchmark($batchSize, $repository);
        echo "Sql with LRU cache $elapsedTime" . PHP_EOL;
    }

    private function sqlWithoutCacheBatchCall(int $batchSize)
    {
        $repository =  $this->getContainer()->get('pim_catalog.repository.sql_attribute');
        $elapsedTime = $this->benchmarkBatch($batchSize, $repository);
        echo "Sql without cache in batch call $elapsedTime" . PHP_EOL;
    }

    private function sqlWithLruCacheBatchCall(int $batchSize, int $cacheSize)
    {
        $repository =  new LruAttributeRepository($this->getContainer()->get('pim_catalog.repository.sql_attribute'), $cacheSize);
        $elapsedTime = $this->benchmarkBatch($batchSize, $repository);
        echo "Sql with LRU cache in batch call $elapsedTime" . PHP_EOL;
    }

    private function sqlWithArrayLruCacheBatchCall(int $batchSize, int $cacheSize)
    {
        $repository =  new LruArrayAttributeRepository($this->getContainer()->get('pim_catalog.repository.sql_attribute'), $cacheSize);
        $elapsedTime = $this->benchmarkBatch($batchSize, $repository);
        echo "Sql with LRU cache in batch call $elapsedTime" . PHP_EOL;
    }

    private function benchmark(int $batchSize, $repository)
    {
        $sql = 'SELECT raw_values FROM pim_catalog_product LIMIT %s OFFSET %s';

        $elapsedTime = 0;
        $offset = 0;
        $count = 0;
        $allAttributeCodes = [];
        do {
            echo "$offset done" . PHP_EOL;
            $rows = $this->getConnection()->executeQuery(sprintf($sql, $batchSize, $offset))->fetchAll();
            $attributeCodes = [];
            foreach ($rows as $row) {
                foreach (json_decode($row['raw_values'], true) as $attributeCode => $values) {
                    $attributeCodes[] = $attributeCode;
                    $allAttributeCodes[$attributeCode] = 1;
                }

                $count++;
            }

            $start = microtime(true);
            foreach ($attributeCodes as $attributeCode) {
                $repository->findOneByIdentifier($attributeCode);
            }
            $elapsedTime += microtime(true) - $start;

            $clearer =  $this->getContainer()->get('pim_connector.doctrine.cache_clearer');
            $clearer->clear();
            $offset += $batchSize;
        } while (!empty($rows) && $count < 80000);

        echo count($allAttributeCodes) . ' distinct attribute codes' . PHP_EOL;

        return $elapsedTime;
    }

    private function benchmarkBatch(int $batchSize, $repository)
    {
        $sql = 'SELECT raw_values FROM pim_catalog_product LIMIT %s OFFSET %s';

        $elapsedTime = 0;
        $offset = 0;
        $count = 0;
        $allAttributeCodes = [];

        do {
            echo "$offset done" . PHP_EOL;
            $rows = $this->getConnection()->executeQuery(sprintf($sql, $batchSize, $offset))->fetchAll();
            foreach ($rows as $row) {
                $attributeCodes = array_keys(json_decode($row['raw_values'], true));

                $start = microtime(true);
                $attributes = $repository->findSeveralByIdentifiers($attributeCodes);
                $elapsedTime += microtime(true) - $start;
                Assert::count($attributes, count($attributeCodes));

                foreach ($attributeCodes as $attributeCode) {
                    $allAttributeCodes[$attributeCode] = 1;
                }

                $count++;
            }
            $offset += $batchSize;
        } while (!empty($rows) && $count < 80000);

        echo count($allAttributeCodes) . ' distinct attribute codes' . PHP_EOL;

        return $elapsedTime;
    }

    private function getConnection(): Connection
    {
        return $this->getContainer()->get('database_connection');
    }
}
