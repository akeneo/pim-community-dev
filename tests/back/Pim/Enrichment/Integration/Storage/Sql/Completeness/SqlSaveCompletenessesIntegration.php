<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness\SqlSaveCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\UuidInterface;

final class SqlSaveCompletenessesIntegration extends TestCase
{
    public function test_that_it_saves_a_product_completenesses(): void
    {
        $productUuid = $this->createProduct('a_great_product');
        $collection = new ProductCompletenessWithMissingAttributeCodesCollection($productUuid->toString(), [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, [])
        ]);
        $this->executeSave($collection);

        $dbCompletenesses = $this->getCompletenessesFromDB($productUuid);
        Assert::assertCount(1, $dbCompletenesses);
        Assert::assertEquals(
            [
                'ecommerce' => [
                    'en_US' => [
                        'missing' => 0,
                        'required' => 5,
                    ]
                ]
            ],
            $dbCompletenesses,
        );
    }

    public function test_that_it_saves_completenesses(): void
    {
        $productUuid = $this->createProduct('a_great_product');

        $collection = new ProductCompletenessWithMissingAttributeCodesCollection($productUuid->toString(), [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['a_text']),
            new ProductCompletenessWithMissingAttributeCodes(
                'tablet',
                'fr_FR',
                10,
                [
                    'a_localized_and_scopable_text_area',
                    'a_yes_no',
                    'a_multi_select',
                    'a_file',
                    'a_price',
                    'a_number_float',
                ]
            ),
        ]);

        $this->executeSave($collection);

        $dbCompletenesses = $this->getCompletenessesFromDB($productUuid);
        Assert::assertCount(2, $dbCompletenesses);
        Assert::assertEquals(
            [
                'ecommerce' => [
                    'en_US' => [
                        'missing' => 1,
                        'required' => 5,
                    ]
                ],
                'tablet' => [
                    'fr_FR' => [
                        'missing' => 6,
                        'required' => 10,
                    ]
                ],
            ],
            $dbCompletenesses,
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function executeSave(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): void
    {
        $this->get(SqlSaveCompletenesses::class)->save($completenesses);
    }

    private function connection(): Connection
    {
        return $this->get('database_connection');
    }

    private function createProduct(string $identifier): UuidInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, 'familyA');
        $this->get('pim_catalog.saver.product')->save($product);

        return $product->getUuid();
    }

    private function getCompletenessesFromDB(UuidInterface $productUuid): array
    {
        $sql = <<<SQL
SELECT completeness
FROM pim_catalog_product_completeness completeness
WHERE product_uuid = :productUuid
SQL;
        $result = $this->connection()->executeQuery($sql, ['productUuid' => $productUuid->getBytes()])->fetchOne();

        return \json_decode($result, true);
    }
}
