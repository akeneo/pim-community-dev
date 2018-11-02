<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Sql\LruAttributeRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $batchSize = 100;

        $this->doctrineWithoutCache($batchSize);
        $this->doctrineWithCache($batchSize);
        $this->sqlWithoutCache($batchSize);
        $this->sqlWithLruCache($batchSize, 20);
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
        $repository =  new LruAttributeRepository($this->getContainer()->get('pim_catalog.repository.sql_attribute'), $cacheSize);
        $elapsedTime = $this->benchmark($batchSize, $repository);
        echo "Sql with LRU cache $elapsedTime" . PHP_EOL;
    }

    private function benchmark(int $batchSize, $repository)
    {
        $sql = 'SELECT raw_values FROM pim_catalog_product LIMIT %s OFFSET %s';

        $elapsedTime = 0;
        $offset = 0;
        do {
            $rows = $this->getConnection()->executeQuery(sprintf($sql, $batchSize, $offset))->fetchAll();
            $attributeCodes = [];
            foreach ($rows as $row) {
                foreach (json_decode($row['raw_values'], true) as $attributeCode => $values) {
                    $attributeCodes[] = $attributeCode;
                }
            }

            $start = microtime(true);
            foreach ($attributeCodes as $attributeCode) {
                $repository->findOneByIdentifier($attributeCode);
            }
            $elapsedTime += microtime(true) - $start;

            $clearer =  $this->getContainer()->get('pim_connector.doctrine.cache_clearer');
            $clearer->clear();
            $offset += $batchSize;
        } while (!empty($rows));

        return $elapsedTime;
    }

    private function getConnection(): Connection
    {
        return $this->getContainer()->get('database_connection');
    }
}
