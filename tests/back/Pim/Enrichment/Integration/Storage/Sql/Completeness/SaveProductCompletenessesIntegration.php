<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Completeness;

use Akeneo\Channel\Component\Model\ChannelInterface;
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
    public function test_that_it_saves_a_product_completenesses(): void
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
SELECT completeness
FROM pim_catalog_product_completeness completeness
WHERE product_id = :productId
SQL;
        $result = $this->get('database_connection')->executeQuery($sql, ['productId' => $productId])->fetchOne();

        return \json_decode($result, true);
    }

    private function createChannel(string $code, array $data = []): ChannelInterface
    {
        $defaultData = [
            'code' => $code,
            'locales' => ['en_US'],
            'currencies' => ['USD'],
            'category_tree' => 'master',
        ];
        $data = array_merge($defaultData, $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($code);
        if (null === $channel) {
            $channel = $this->get('pim_catalog.factory.channel')->create();
        }

        $this->get('pim_catalog.updater.channel')->update($channel, $data);
        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.channel')->saveAll([$channel]);

        return $channel;
    }

    private function removeChannel(string $code): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($code);
        $this->get('pim_catalog.remover.channel')->remove($channel);
    }
}
