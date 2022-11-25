<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class ChunkProductModelCodesIntegration extends TestCase
{
    public function test_it_batches_by_product_model_raw_values_size()
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
        $codes = $this->givenProductModelsWithRawValuesSize($productSizes);

        $chunkProductModelCodes= $this->get('Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\ChunkProductModelCodes')->byRawValuesSize($codes, 2000000);

        Assert::same($chunkProductModelCodes, [
            array_slice($codes, 0, 5),
            array_slice($codes, 5, 2),
            array_slice($codes, 7, 2),
            array_slice($codes, 9, 1),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenProductModelsWithRawValuesSize(array $productModelSizes): array
    {
        $codes = [];
        $dbalConnection = $this->get('database_connection');
        $sqlInsert = <<<SQL
INSERT INTO `pim_catalog_product_model` (`code`, `created`, `updated`, `raw_values`) VALUES
(:code,  '1990-09-05 00:00:00',  '1990-09-05 00:00:00',  :raw_values)
SQL;

        $dbalConnection->beginTransaction();
        foreach ($productModelSizes as $productModelSize) {
            // "a" character is encoded as 1 bytes in UTF-8
            // -10 is for the size of the characters [""] in the json
            $data = str_repeat("a", $productModelSize - 10);
            $code = 'code_' . count($codes);
            $codes[] = $code;
            $dbalConnection->executeQuery($sqlInsert, ['code' => $code, 'raw_values' => "[\"$data\"]"]);
        }

        $dbalConnection->commit();

        return $codes;
    }
}
