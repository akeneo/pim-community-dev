<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TmpAddUniqueData extends Command
{
    protected static $defaultName = 'pim:product:add-unique';

    public function __construct(
        private Connection $connection
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addTableIfNotExist();

        $prefixes = [
            'AKN-' => [
                'products' => 1000000,
                'gap_probability' => 0, // SUR 100
                'suffix' => '',
            ],
            'SHOES-red-' => [
                'products' => 1000000,
                'gap_probability' => 50,
                'suffix' => '-SOMETHING'
            ],
        ];

        foreach ($prefixes as $prefix => $data) {
            $this->removeProductsWithPrefix($prefix);
            $this->insertProducts($prefix, $data['products'], $data['gap_probability'], $data['suffix']);
        }

        $this->fillIndexTable();

        return 0;
    }

    private function removeProductsWithPrefix(string $prefix): void
    {
        $sql2 = 'DELETE P FROM pim_catalog_product_unique_data P JOIN pim_catalog_product ON P.product_uuid=pim_catalog_product.uuid WHERE identifier LIKE "%s"';
        $query = sprintf($sql2, $prefix . '%');
        var_dump($query);
        $this->connection->executeQuery($query);

        $sql = 'DELETE FROM pim_catalog_product WHERE identifier LIKE "%s"';
        $query = sprintf($sql, $prefix . '%');
        var_dump($query);
        $this->connection->executeQuery($query);
    }

    private function insertProducts(string $prefix, int $productCount, int $gapProbability, string $suffix): void
    {
        $maxIdSql = 'SELECT MAX(id) from pim_catalog_product';
        $maxId = $this->connection->fetchOne($maxIdSql);

        $identifierId = $this->getIdentifierId();

        $sql = 'INSERT INTO pim_catalog_product (id, uuid, family_id, product_model_id, family_variant_id, is_enabled, identifier, raw_values, created, updated) VALUES';
        $uniqueSql = 'INSERT INTO pim_catalog_product_unique_data (id, product_uuid, attribute_id, raw_data) VALUES';

        $totalGaps = 0;
        for ($i = 0; $i < $productCount; $i++) {
            $gap = 0;
            if (random_int(0, 99) < $gapProbability) {
                $gap = 10;
            }
            $totalGaps = $totalGaps + $gap;
            $productId = $i + $maxId + 1;
            $uuid = Uuid::uuid4();
            $identifier = $prefix . ($i + $totalGaps) . $suffix;
            $sql .= sprintf(
                "(%d, UUID_TO_BIN('%s'), NULL, NULL, NULL, 1, '%s', '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"%s\"}}}', '2021-05-31 10:42:00', '2021-05-31 10:42:00'),",
                $productId,
                $uuid->toString(),
                $identifier,
                $identifier
            );

            $uniqueSql .= sprintf(
                "(%d, UUID_TO_BIN('%s'), %d, \"%s\"),",
                $productId,
                $uuid->toString(),
                $identifierId,
                $identifier
            );

            if ($i % 1000 === 0) {
                $this->connection->executeQuery(rtrim($sql, ','));
                $this->connection->executeQuery(rtrim($uniqueSql, ','));
                $sql = 'INSERT INTO pim_catalog_product (id, uuid, family_id, product_model_id, family_variant_id, is_enabled, identifier, raw_values, created, updated) VALUES';
                $uniqueSql = 'INSERT INTO pim_catalog_product_unique_data (id, product_uuid, attribute_id, raw_data) VALUES';
            }
        }

        $this->connection->executeQuery(rtrim($sql, ','));
        $this->connection->executeQuery(rtrim($uniqueSql, ','));
    }

    private function addTableIfNotExist(): void
    {
        $createTableSql = <<<SQL
CREATE TABLE IF NOT EXISTS identifier_prefix (
    `attribute_id` int(11) NOT NULL,
    `product_uuid` BINARY(16) NOT NULL,
    `prefix` VARCHAR(255) NOT NULL,
    `number` int(11) NOT NULL,
    CONSTRAINT `attribute` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product` FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE,
    INDEX status_index (attribute_id, prefix, number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->connection->executeQuery($createTableSql);

        $dropSql = <<<SQL
DELETE FROM identifier_prefix;
SQL;

        $this->connection->executeQuery($dropSql);


    }

    private function fillIndexTable()
    {
        $attributeId = $this->getIdentifierId();
        $shouldContinue = true;
        $lastUuid = null;
        while ($shouldContinue) {
            $shouldContinue = false;
            $sql = 'SELECT BIN_TO_UUID(uuid) AS uuid, identifier FROM pim_catalog_product %s ORDER BY uuid LIMIT 1000';
            if ($lastUuid) {
                $sql = sprintf($sql, 'WHERE uuid > UUID_TO_BIN("' . $lastUuid->toString() . '")');
            } else {
                $sql = sprintf($sql, '');
            }
            foreach ($this->connection->fetchAllAssociative($sql) as $result) {
                $prefixesAndNumbers = $this->getPrefixesAndNumbers($result['identifier']);
                foreach ($prefixesAndNumbers as $prefix => $number) {
                    $insertSql = 'INSERT INTO identifier_prefix (attribute_id, product_uuid, prefix, number) VALUES (%d, UUID_TO_BIN("%s"), "%s", %d)';
                    $this->connection->executeQuery(sprintf(
                        $insertSql,
                        $attributeId,
                        $result['uuid'],
                        $prefix,
                        $number
                    ));
                }
                $lastUuid = Uuid::fromString($result['uuid']);
                $shouldContinue = true;
            };
        }
    }

    private function getIdentifierId(): int
    {
        $identifierIdSql = 'SELECT id FROM pim_catalog_attribute WHERE code="sku"';
        return \intval($this->connection->fetchOne($identifierIdSql));
    }

    /**
     * Returns the prefix and their associated number
     * Ex: "AKN-2012" will return ["AKN-" => 2012, "AKN-2" => 12, "AKN-20" => 12, "AKN-201" => 2]
     */
    private function getPrefixesAndNumbers(mixed $identifier)
    {
        $results = [];
        for ($i = 0; $i < strlen($identifier); $i++) {
            $charAtI = substr($identifier, $i, 1);
            if (is_numeric($charAtI)) {
                $prefix = substr($identifier, 0, $i);
                $results[$prefix] = $this->getAllBeginningNumbers(substr($identifier, $i));
            }
        }
        return $results;
    }

    /**
     * Returns all the beginning numbers from a string
     * Ex: "251-toto" will return 251
     */
    private function getAllBeginningNumbers(string $identifierFromAnInteger)
    {
        $result = '';
        $i = 0;
        while (is_numeric(substr($identifierFromAnInteger, $i, 1))) {
            $result = $result . substr($identifierFromAnInteger, $i, 1);
            $i++;
        }
        return \intval($result);
    }
}
