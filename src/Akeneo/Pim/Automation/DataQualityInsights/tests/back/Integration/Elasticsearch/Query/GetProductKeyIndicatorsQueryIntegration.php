<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Query\GetProductKeyIndicatorsQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductKeyIndicatorsQueryIntegration extends DataQualityInsightsTestCase
{
    private Client $esClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $this->createMinimalFamilyAndFamilyVariant('family_V', 'family_V_1');
    }

    public function test_it_retrieves_key_indicators_for_all_the_products()
    {
        $this->createProductModel('product_model_with_perfect_enrichment', 'family_V_1');
        $this->createProductModel('product_model_with_missing_enrichment', 'family_V_1');

        $this->givenProductsWithoutValues(4);
        $this->givenAProductVariantWithoutValues('product_model_with_missing_enrichment');

        $this->givenAProductWithPerfectEnrichmentAndImage();
        $this->givenAProductWithPerfectEnrichmentButWithoutAttributeImage();
        $this->givenAProductWithImageButMissingEnrichment();
        $this->givenAProductVariantWithPerfectEnrichmentAndImage('product_model_with_perfect_enrichment');
        $this->givenAProductVariantWithPerfectEnrichmentButWithoutAttributeImage('product_model_with_perfect_enrichment');

        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');

        $expectedKeyIndicators = [
            'good_enrichment' => new KeyIndicator($goodEnrichment, 4, 6),
            'has_image' => new KeyIndicator($hasImage, 3, 7),
        ];

        $keyIndicators = $this->get(GetProductKeyIndicatorsQuery::class)->all(new ChannelCode('ecommerce'), new LocaleCode('en_US'), $goodEnrichment, $hasImage);

        $this->assertEquals($expectedKeyIndicators, $keyIndicators);
    }

    private function givenProductsWithoutValues(int $nbProducts): void
    {
        for ($i = 1; $i <= $nbProducts; $i++) {
            $product = $this->createProduct(sprintf('product_without_values_%d', $i));

            $this->updateProductKeyIndicators($product->getId(), false, false);
        }
    }

    private function givenAProductWithPerfectEnrichmentAndImage(): void
    {
        $product = $this->createProduct('product_with_perfect_enrichment_and_image');

        $this->updateProductKeyIndicators($product->getId(), true, true);
    }

    private function givenAProductWithPerfectEnrichmentButWithoutAttributeImage(): void
    {
        $product = $this->createProduct('product_with_perfect_enrichment_but_without_attribute_image');

        $this->updateProductKeyIndicators($product->getId(), true, false);
    }

    private function givenAProductWithImageButMissingEnrichment(): void
    {
        $product = $this->createProduct('product_with_image_but_missing_enrichment');

        $this->updateProductKeyIndicators($product->getId(), false, true);
    }

    private function givenAProductVariantWithoutValues(string $parent): void
    {
        $productVariant = $this->createMinimalProductVariant(
            'product_variant_without_values',
            $parent,
            DataQualityInsightsTestCase::MINIMAL_VARIANT_OPTIONS[0]
        );

        $this->updateProductKeyIndicators($productVariant->getId(), false, false);
    }

    private function givenAProductVariantWithPerfectEnrichmentAndImage(string $parent): void
    {
        $productVariant = $this->createMinimalProductVariant(
            'product_variant_with_perfect_enrichment',
            $parent,
            DataQualityInsightsTestCase::MINIMAL_VARIANT_OPTIONS[1]
        );

        $this->updateProductKeyIndicators($productVariant->getId(), true, true);
    }

    private function givenAProductVariantWithPerfectEnrichmentButWithoutAttributeImage(string $parent): void
    {
        $productVariant = $this->createMinimalProductVariant(
            'product_variant_with_perfect_enrichment_but_without_attribute_image',
            $parent,
            DataQualityInsightsTestCase::MINIMAL_VARIANT_OPTIONS[2]
        );

        $this->updateProductKeyIndicators($productVariant->getId(), true, false);
    }

    private function updateProductKeyIndicators(int $productId, bool $goodEnrichment, bool $hasImage): void
    {
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $keyIndicators = [
            'ecommerce' => [
                'en_US' => [
                    'good_enrichment' => $goodEnrichment,
                    'has_image' => $hasImage,
                ],
                'fr_FR' => [
                    'good_enrichment' => !$goodEnrichment,
                    'has_image' => !$hasImage,
                ],
            ],
            'mobile' => [
                'en_US' => [
                    'good_enrichment' => !$goodEnrichment,
                    'has_image' => !$hasImage,
                ],
            ],
        ];

        $this->esClient->refreshIndex();
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'inline' => "ctx._source.rates = params.rates; ctx._source.data_quality_insights = params.data_quality_insights;",
                    'params' => [
                        'rates' => [],
                        'data_quality_insights' => ['key_indicators' => $keyIndicators],
                    ],
                ],
                'query' => [
                    'term' => [
                        'id' => sprintf('product_%d', $productId),
                    ],
                ],
            ]
        );

        $this->esClient->refreshIndex();
    }
}
