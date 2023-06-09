<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetProductCompletenessRatioIntegration extends TestCase
{
    /** @var GetProductCompletenesses */
    public $getProductCompletenesses;

    /** @var GetProductCompletenessRatio */
    private $getProductCompletenessRatio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getProductCompletenesses = $this->get(
            'akeneo.pim.enrichment.product.query.get_product_completenesses'
        );
        $this->getProductCompletenessRatio = $this->get(
            'akeneo.pim.enrichment.product.query.product_completeness_ratio'
        );
    }

    /**
     * @test
     */
    public function it_returns_the_completeness_ratio_of_a_product_for_a_given_channel_and_locale()
    {
        $product = $this->createProduct();
        $completenesses = $this->getProductCompletenesses->fromProductId($product->getId());

        Assert::assertNotEmpty($completenesses);

        /** @var ProductCompleteness $completeness */
        foreach ($completenesses as $completeness) {
            $ratio = $this->getProductCompletenessRatio->forChannelCodeAndLocaleCode(
                $product->getId(),
                $completeness->channelCode(),
                $completeness->localeCode()
            );
            Assert::assertNotNull($ratio);
            Assert::assertEquals($completeness->ratio(), $ratio);
        }
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_completeness_is_not_calculated_yet()
    {
        $product = $this->createProduct();
        $this->get('database_connection')->executeUpdate(
            'DELETE FROM pim_catalog_completeness WHERE product_id = :productId',
            [
                'productId' => $product->getId(),
            ]
        );
        $this->get('database_connection')->executeUpdate(
            'DELETE FROM pim_catalog_product_completeness WHERE product_id = :productId',
            [
                'productId' => $product->getId(),
            ]
        );

        Assert::assertNull(
            $this->getProductCompletenessRatio->forChannelCodeAndLocaleCode($product->getId(), 'ecommerce', 'en_US')
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(): ProductInterface
    {
        $product = new Product();
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'family' => 'familyA',
                'values' => [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'test_completeness',
                        ],
                    ],
                    'a_date' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => '2020-03-18T00:00:00+00:00',
                        ],
                    ],
                    'a_text' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'lorem ipsum',
                        ],
                    ],
                    'a_yes_no' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => false,
                        ],
                    ],
                    'a_scopable_price' => [
                        [
                            'scope' => 'ecommerce',
                            'locale' => null,
                            'data' => [
                                ['amount' => '10.00', 'currency' => 'EUR'],
                                ['amount' => '12.00', 'currency' => 'USD'],
                            ],
                        ],
                    ],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'en_US',
                            'data' => 'Lorem ipsum dolor sit amet',
                        ],
                    ],
                ],
            ]
        );

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
