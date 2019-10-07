<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveProductCompletenessesIntegration extends TestCase
{
    public function test_that_it_clears_existing_completenesses_and_missing_attributes_if_provided_completenesses_are_empty()
    {
        $productId = $this->createProduct('a_great_product');
        Assert::assertNotEmpty($this->getCompletenessesFromDB($productId));

        $this->executeSave(new ProductCompletenessWithMissingAttributeCodesCollection($productId, []));
        Assert::assertEmpty($this->getCompletenessesFromDB($productId));
    }

    public function test_that_it_saves_completenesses_given_a_product_id()
    {
        $productId = $this->createProduct('a_great_product');
        $collection = new ProductCompletenessWithMissingAttributeCodesCollection($productId, [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, [])
        ]);
        $this->executeSave($collection);

        $dbCompletenesses = $this->getCompletenessesFromDB($productId);
        Assert::assertCount(1, $dbCompletenesses);
        Assert::assertEquals(
            [
                'channel_code' => 'ecommerce',
                'locale_code' => 'en_US',
                'missing_count' => 0,
                'required_count' => 5,
            ],
            $dbCompletenesses['ecommerce-en_US']
        );
    }

    public function test_that_it_saves_completenesses()
    {
        $productId = $this->createProduct('a_great_product');

        $collection = new ProductCompletenessWithMissingAttributeCodesCollection($productId, [
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

        $dbCompletenesses = $this->getCompletenessesFromDB($productId);
        Assert::assertCount(2, $dbCompletenesses);
        Assert::assertEquals(
            [
                'channel_code' => 'ecommerce',
                'locale_code' => 'en_US',
                'missing_count' => 1,
                'required_count' => 5,
            ],
            $dbCompletenesses['ecommerce-en_US']
        );
        Assert::assertEquals(
            [
                'channel_code' => 'tablet',
                'locale_code' => 'fr_FR',
                'missing_count' => 6,
                'required_count' => 10,
            ],
            $dbCompletenesses['tablet-fr_FR']
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function executeSave(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): void
    {
        $this->get('akeneo.pim.enrichment.product.query.save_product_completenesses')->save($completenesses);
    }

    private function createProduct(string $identifier): int
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, 'familyA');
        $this->get('pim_catalog.saver.product')->save($product);

        return $product->getId();
    }

    private function getCompletenessesFromDB(int $productId): array
    {
        $sql = <<<SQL
SELECT channel.code as channel_code, locale.code as locale_code, completeness.missing_count, completeness.required_count
FROM pim_catalog_completeness completeness
    INNER JOIN pim_catalog_channel channel on channel.id = completeness.channel_id
    INNER JOIN pim_catalog_locale locale on locale.id = completeness.locale_id
WHERE product_id = :productId
SQL;
        $results = [];
        $rows = $this->get('database_connection')->executeQuery($sql, ['productId' => $productId])->fetchAll();
        foreach ($rows as $row) {
            $key = sprintf('%s-%s', $row['channel_code'], $row['locale_code']);
            $results[$key] = $row;
        }

        return $results;
    }
}
