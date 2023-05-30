<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Completeness;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductCompletenessesIntegration extends TestCase
{
    private Connection $connection;

    private ChannelExistsWithLocaleInterface $channelExistsWithLocale;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->channelExistsWithLocale = $this->get('pim_channel.query.cache.channel_exists_with_locale');
    }

    public function test_that_it_returns_completenesseses_given_a_product_uuid(): void
    {
        $this->createProduct(
            'productA',
            'familyA3',
            [
                'a_yes_no' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => false,
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                        'data' => 'A great description',
                    ],
                    [
                        'scope' => 'tablet',
                        'locale' => 'fr_FR',
                        'data' => 'Une super description',
                    ],
                ],
            ]
        );

        $completenesses = $this->getCompletenesses($this->getProductUuid('productA'));
        // ecommerce + en_US
        // tablet + (en_US, de_DE, fr_FR)
        // ecommerce_china + (en_US, zh_CN)
        Assert::assertCount(6, $completenesses);
        $this->assertCompletenessContains($completenesses, 'ecommerce', 'en_US', 4, 1);
        $this->assertCompletenessContains($completenesses, 'tablet', 'en_US', 4, 2);
    }

    public function test_it_returns_completenesses_of_several_product_uuids(): void
    {
        $this->initProducts();

        $idProdA = $this->getProductUuid('productA');
        $idProdA2 = $this->getProductUuid('productA2');
        $completenesses = $this
            ->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
            ->fromProductUuids([$idProdA, $idProdA2]);

        Assert::assertCount(2, $completenesses);

        Assert::assertArrayHasKey($idProdA->toString(), $completenesses);
        Assert::assertCount(6, $completenesses[$idProdA->toString()]);
        $this->assertCompletenessContains($completenesses[$idProdA->toString()], 'ecommerce', 'en_US', 4, 1);
        $this->assertCompletenessContains($completenesses[$idProdA->toString()], 'tablet', 'en_US', 4, 2);

        Assert::assertArrayHasKey($idProdA2->toString(), $completenesses);
        Assert::assertCount(6, $completenesses[$idProdA2->toString()]);
        $this->assertCompletenessContains($completenesses[$idProdA2->toString()], 'ecommerce', 'en_US', 4, 1);
        $this->assertCompletenessContains($completenesses[$idProdA2->toString()], 'tablet', 'en_US', 4, 2);
    }

    public function test_it_returns_completenesses_of_several_product_uuids_filtered_by_channel_and_locales(): void
    {
        $this->initProducts();
        $idProdA = $this->getProductUuid('productA');
        $idProdA2 = $this->getProductUuid('productA2');
        $completenesses = $this
            ->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
            ->fromProductUuids([$idProdA, $idProdA2], 'ecommerce_china', ['en_US', 'fr_FR']);

        Assert::assertCount(2, $completenesses);

        Assert::assertArrayHasKey($idProdA->toString(), $completenesses);
        Assert::assertCount(1, $completenesses[$idProdA->toString()]);
        $this->assertCompletenessContains($completenesses[$idProdA->toString()], 'ecommerce_china', 'en_US', 1, 0);

        Assert::assertArrayHasKey($idProdA2->toString(), $completenesses);
        Assert::assertCount(1, $completenesses[$idProdA2->toString()]);
        $this->assertCompletenessContains($completenesses[$idProdA2->toString()], 'ecommerce_china', 'en_US', 1, 0);
    }

    public function test_it_returns_completenesses_of_several_product_uuids_filtered_by_channel(): void
    {
        $this->initProducts();
        $idProdA = $this->getProductUuid('productA');
        $idProdA2 = $this->getProductUuid('productA2');
        $completenesses = $this
            ->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
            ->fromProductUuids([$idProdA, $idProdA2], 'ecommerce_china');

        Assert::assertCount(2, $completenesses);

        Assert::assertArrayHasKey($idProdA->toString(), $completenesses);
        Assert::assertCount(2, $completenesses[$idProdA->toString()]);
        $this->assertCompletenessContains($completenesses[$idProdA->toString()], 'ecommerce_china', 'en_US', 1, 0);
        $this->assertCompletenessContains($completenesses[$idProdA->toString()], 'ecommerce_china', 'zh_CN', 1, 0);

        Assert::assertArrayHasKey($idProdA2->toString(), $completenesses);
        Assert::assertCount(2, $completenesses[$idProdA2->toString()]);
        $this->assertCompletenessContains($completenesses[$idProdA2->toString()], 'ecommerce_china', 'en_US', 1, 0);
        $this->assertCompletenessContains($completenesses[$idProdA2->toString()], 'ecommerce_china', 'zh_CN', 1, 0);
    }

    public function test_it_returns_completenesses_of_several_product_uuids_filtered_by_locale(): void
    {
        $this->initProducts();
        $idProdA = $this->getProductUuid('productA');
        $idProdA2 = $this->getProductUuid('productA2');
        $completenesses = $this
            ->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
            ->fromProductUuids([$idProdA, $idProdA2], null, ['zh_CN', 'fr_FR']);

        Assert::assertCount(2, $completenesses);

        Assert::assertArrayHasKey($idProdA->toString(), $completenesses);
        Assert::assertCount(2, $completenesses[$idProdA->toString()]);
        $this->assertCompletenessContains($completenesses[$idProdA->toString()], 'tablet', 'fr_FR', 4, 1);
        $this->assertCompletenessContains($completenesses[$idProdA->toString()], 'ecommerce_china', 'zh_CN', 1, 0);

        Assert::assertArrayHasKey($idProdA2->toString(), $completenesses);
        Assert::assertCount(2, $completenesses[$idProdA2->toString()]);
        $this->assertCompletenessContains($completenesses[$idProdA2->toString()], 'tablet', 'fr_FR', 4, 2);
        $this->assertCompletenessContains($completenesses[$idProdA2->toString()], 'ecommerce_china', 'zh_CN', 1, 0);
    }

    public function test_that_it_returns_an_empty_array_for_a_product_without_family(): void
    {
        $this->createProduct(
            'product_without_family',
            null,
            [
                'a_text' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'Lorem ipsum dolor sit amet',
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                        'data' => 'A great description',
                    ],
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'fr_FR',
                        'data' => 'Une super description',
                    ],
                ],
            ]
        );

        $completenesses = $this->getCompletenesses($this->getProductUuid('product_without_family'));
        Assert::assertSame(0, $completenesses->count());
    }

    public function test_it_filters_non_existing_channels(): void
    {
        $this->initProducts();

        $this->removeChannel('ecommerce_china');

        $idProd = $this->getProductUuid('productA');

        $completenesses = $this
            ->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
            ->fromProductUuids([$idProd], 'ecommerce_china', ['en_US']);

        Assert::assertCount(1, $completenesses);

        Assert::assertArrayHasKey($idProd->toString(), $completenesses);
        Assert::assertCount(0, $completenesses[$idProd->toString()]);
    }

    public function test_it_filters_locales_not_bound_to_channels(): void
    {
        $this->initProducts();

        $this->removeLocaleFromChannel('de_DE', 'tablet');

        $idProd = $this->getProductUuid('productA');

        $completenesses = $this
            ->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
            ->fromProductUuids([$idProd], 'tablet');

        Assert::assertCount(1, $completenesses);

        Assert::assertArrayHasKey($idProd->toString(), $completenesses);
        Assert::assertCount(2, $completenesses[$idProd->toString()]);
        Assert::assertNull($completenesses[$idProd->toString()]->getCompletenessForChannelAndLocale('tablet', 'de_DE'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function initProducts(): void
    {
        $this->createProduct(
            'productA',
            'familyA3',
            [
                'a_yes_no' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => false,
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                        'data' => 'A great description',
                    ],
                    [
                        'scope' => 'tablet',
                        'locale' => 'fr_FR',
                        'data' => 'Une super description',
                    ],
                ],
            ]
        );
        $this->createProduct(
            'productA2',
            'familyA3',
            [
                'a_yes_no' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => true,
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                        'data' => 'An amazing description',
                    ],
                    [
                        'scope' => 'tablet',
                        'locale' => 'fr_FR',
                        'data' => null,
                    ],
                ],
            ]
        );
    }

    private function createProduct(string $identifier, ?string $familyCode, array $values): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function getCompletenesses(UuidInterface $productUuid): ProductCompletenessCollection
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
                    ->fromProductUuid($productUuid);
    }

    private function assertCompletenessContains(
        ProductCompletenessCollection $completenesses,
        string $channelCode,
        string $localeCode,
        int $requiredCount,
        int $missingCount
    ): void {
        foreach ($completenesses as $completeness) {
            if ($completeness->channelCode() === $channelCode && $completeness->localeCode() === $localeCode) {
                Assert::assertSame($requiredCount, $completeness->requiredCount());
                Assert::assertSame($missingCount, $completeness->missingCount());

                return;
            }
        }

        throw new ExpectationFailedException(
            sprintf(
                'Failed assering that completenesses contain an element with channel "%s" and locale "%s"',
                $channelCode,
                $localeCode
            )
        );
    }

    private function removeLocaleFromChannel(string $localeCode, string $channelCode): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        foreach($channel->getLocales() as $locale) {
            if ($locale->getCode() === $localeCode) {
                $channel->removeLocale($locale);
            }
        }
        $this->get('pim_catalog.saver.channel')->save($channel);

        $this->channelExistsWithLocale->clearCache();
    }

    private function removeChannel(string $channelCode): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        $this->get('pim_catalog.remover.channel')->remove($channel);

        $this->channelExistsWithLocale->clearCache();
    }
}
