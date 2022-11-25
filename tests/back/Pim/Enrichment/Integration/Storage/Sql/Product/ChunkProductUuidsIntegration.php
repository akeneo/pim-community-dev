<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class ChunkProductUuidsIntegration extends TestCase
{
    public function test_it_batches_by_product_raw_value_size()
    {
        $productSizes = [
            // -- cumulated size: 1500000
            100000,
            200000,
            300000,
            400000,
            500000,

            // -- cumulated size: 1300000
            600000,
            700000,

            // -- cumulated size: 1700000
            800000,
            900000,

            // -- cumulated size: 1000000
            1000000
        ];
        $uuids = $this->givenProductsWithRawValuesSize($productSizes);
        $uuidsAString = array_map(fn (UuidInterface $uuid): string => $uuid->toString(), $uuids);

        $chunkProductUuids = $this->get('Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\ChunkProductUuids')->byRawValuesSize($uuids, 2000000);
        $chunkProductUuidsAsString =  array_map(
            fn ($array) => array_map(fn (UuidInterface $uuid): string => $uuid->toString(), $array),
            $chunkProductUuids
        );

        Assert::same($chunkProductUuidsAsString, [
            array_slice($uuidsAString, 0, 5),
            array_slice($uuidsAString, 5, 2),
            array_slice($uuidsAString, 7, 2),
            array_slice($uuidsAString, 9, 1),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenProductsWithRawValuesSize(array $productSizes)
    {
        $uuids = [];
        $dbalConnection = $this->get('database_connection');
        $sqlInsert = <<<SQL
INSERT INTO `pim_catalog_product` (`uuid`, `is_enabled`, `created`, `updated`, `raw_values`) VALUES
(:uuid, 0,  '1990-09-05 00:00:00',  '1990-09-05 00:00:00',  :raw_values)
SQL;

        $dbalConnection->beginTransaction();
        foreach ($productSizes as $productSize) {
            // "a" character is encoded as 1 bytes in UTF-8
            // -10 is for the size of the characters [""] in the json
            $data = str_repeat("a", $productSize - 10);
            $uuid = Uuid::uuid4();
            $uuids[] = $uuid;
            $dbalConnection->executeQuery($sqlInsert, ['uuid' => $uuid->getBytes(), 'raw_values' => "[\"$data\"]"]);
        }

        $dbalConnection->commit();

        return $uuids;
    }
}
