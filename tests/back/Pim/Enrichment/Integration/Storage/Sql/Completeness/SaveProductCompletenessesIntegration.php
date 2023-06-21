<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Completeness;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveProductCompletenessesIntegration extends TestCase
{
    public function test_that_it_does_not_clear_existing_completenesses_and_missing_attributes_if_provided_completenesses_are_empty()
    {
        $productUuid = $this->createProduct('a_great_product');
        Assert::assertCount(6, $this->getCompletenessesFromDB($productUuid));

        $this->executeSave(new ProductCompletenessWithMissingAttributeCodesCollection($productUuid->toString(), []));
        Assert::assertCount(6, $this->getCompletenessesFromDB($productUuid));
    }

    public function test_that_it_clears_non_existing_locales_channels()
    {
        $this->createChannel('my_channel', ['locales' => ['en_US', 'fr_FR']]);
        $productUuid = $this->createProduct('a_great_product');
        Assert::assertCount(8, $this->getCompletenessesFromDB($productUuid));

        $this->removeChannel('my_channel');
        $this->executeSave(new ProductCompletenessWithMissingAttributeCodesCollection($productUuid->toString(), []));
        Assert::assertCount(6, $this->getCompletenessesFromDB($productUuid));
    }

    public function test_that_it_saves_completenesses_given_a_product_id()
    {
        $productUuid = $this->createProduct('a_great_product');
        $collection = new ProductCompletenessWithMissingAttributeCodesCollection($productUuid->toString(), [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, [])
        ]);
        $this->executeSave($collection);

        $dbCompletenesses = $this->getCompletenessesFromDB($productUuid);
        Assert::assertCount(6, $dbCompletenesses);
        Assert::assertEquals(
            [
                'missing' => 0,
                'required' => 5,
            ],
            $dbCompletenesses['ecommerce-en_US']
        );
    }

    public function test_that_it_saves_completenesses()
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
        Assert::assertCount(6, $dbCompletenesses);
        Assert::assertEquals(
            [
                'missing' => 1,
                'required' => 5,
            ],
            $dbCompletenesses['ecommerce-en_US']
        );
        Assert::assertEquals(
            [
                'missing' => 6,
                'required' => 10,
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
FROM pim_catalog_product_completeness
WHERE pim_catalog_product_completeness.product_uuid = :productUuid
SQL;
        $results = [];
        $completenesses = \json_decode(
            $this->get('database_connection')->executeQuery($sql, ['productUuid' => $productUuid->getBytes()])->fetchOne(),
            true
        );

        foreach ($completenesses as $channelCode => $completenessPerChannel) {
            foreach ($completenessPerChannel as $localeCode => $completeness) {
                $key = sprintf('%s-%s', $channelCode, $localeCode);
                $results[$key] = $completeness;
            }
        }

        return $results;
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
