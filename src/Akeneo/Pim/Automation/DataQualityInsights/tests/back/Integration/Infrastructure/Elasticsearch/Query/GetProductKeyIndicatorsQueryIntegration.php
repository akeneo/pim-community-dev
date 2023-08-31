<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Ramsey\Uuid\UuidInterface;

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

    public function test_it_retrieves_key_indicators_for_all_the_products_and_product_models()
    {
        $this->givenAProductWithoutValues();
        $this->givenAProductWithPerfectEnrichmentAndImage();
        $this->givenAProductWithPerfectEnrichmentButWithoutAttributeImage();
        $this->givenAProductWithImageButMissingEnrichment();

        $productModelWithPerfectEnrichment = $this->createProductModel('product_model_with_perfect_enrichment', 'family_V_1');
        $productModelWithMissingEnrichment = $this->createProductModel('product_model_with_missing_enrichment', 'family_V_1');

        $this->givenAProductVariantWithoutValues($productModelWithMissingEnrichment);
        $this->givenAProductVariantWithPerfectEnrichmentAndImage($productModelWithPerfectEnrichment);
        $this->givenAProductVariantWithPerfectEnrichmentButWithoutAttributeImage($productModelWithPerfectEnrichment);

        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');

        $expectedProductsKeyIndicators = [
            'good_enrichment' => new KeyIndicator($goodEnrichment, 4, 3),
            'has_image' => new KeyIndicator($hasImage, 3, 4),
        ];

        $expectedProductModelsKeyIndicators = [
            'good_enrichment' => new KeyIndicator($goodEnrichment, 1, 1),
            'has_image' => new KeyIndicator($hasImage, 0, 2),
        ];

        $productsKeyIndicators = $this->get('akeneo.pim.automation.data_quality_insights.infrastructure.elasticsearch.query.get_product_key_indicators_query')->all(new ChannelCode('ecommerce'), new LocaleCode('en_US'), $goodEnrichment, $hasImage);
        $productModelsKeyIndicators = $this->get('akeneo.pim.automation.data_quality_insights.infrastructure.elasticsearch.query.get_product_model_key_indicators_query')->all(new ChannelCode('ecommerce'), new LocaleCode('en_US'), $goodEnrichment, $hasImage);

        $this->assertEquals($expectedProductsKeyIndicators, $productsKeyIndicators);
        $this->assertEquals($expectedProductModelsKeyIndicators, $productModelsKeyIndicators);
    }

    public function test_it_retrieves_key_indicators_for_the_products_of_a_given_family()
    {
        $this->createFamily('family_A', ['attributes' => []]);
        $this->createFamily('family_B', ['attributes' => []]);

        $this->givenAProductWithoutValues();
        $this->givenAProductWithoutValues([new SetFamily('family_A')]);
        $this->givenAProductWithoutValues([new SetFamily('family_B')]);
        $this->givenAProductWithPerfectEnrichmentAndImage([new SetFamily('family_B')]);
        $this->givenAProductWithImageButMissingEnrichment([new SetFamily('family_A')]);
        $this->givenAProductWithImageButMissingEnrichment([new SetFamily('family_A')]);

        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');

        $expectedProductsKeyIndicators = [
            'good_enrichment' => new KeyIndicator($goodEnrichment, 0, 3),
            'has_image' => new KeyIndicator($hasImage, 2, 1),
        ];

        $productsKeyIndicators = $this->get('akeneo.pim.automation.data_quality_insights.infrastructure.elasticsearch.query.get_product_key_indicators_query')
            ->byFamily(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new FamilyCode('family_A'), $goodEnrichment, $hasImage);
        $productModelsKeyIndicators = $this->get('akeneo.pim.automation.data_quality_insights.infrastructure.elasticsearch.query.get_product_model_key_indicators_query')
            ->byFamily(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new FamilyCode('family_A'), $goodEnrichment, $hasImage);

        $this->assertEquals($expectedProductsKeyIndicators, $productsKeyIndicators);
        $this->assertEmpty($productModelsKeyIndicators);
    }

    public function test_it_retrieves_key_indicators_for_the_products_of_a_given_category()
    {
        $this->createCategory(['code' => 'category_A']);
        $this->createCategory(['code' => 'category_A_1', 'parent' => 'category_A']);
        $this->createCategory(['code' => 'category_B']);

        $this->givenAProductWithoutValues();
        $this->givenAProductWithoutValues([new SetCategories(['category_A', 'category_B'])]);
        $this->givenAProductWithoutValues([new SetCategories(['category_B'])]);
        $this->givenAProductWithPerfectEnrichmentButWithoutAttributeImage([new SetCategories(['category_A'])]);
        $this->givenAProductWithPerfectEnrichmentAndImage([new SetCategories(['category_A_1', 'category_B'])]);
        $this->givenAProductWithImageButMissingEnrichment([new SetCategories(['category_B'])]);

        $productModelA = $this->createProductModel('product_model_A', 'family_V_1', ['categories' => ['category_A']]);
        $productModelB = $this->createProductModel('product_model_B', 'family_V_1', ['categories' => ['category_B']]);

        // Product variant in category B from itself, and in category A from its parent
        $this->givenAProductVariantWithoutValues($productModelA, [new SetCategories(['category_B'])]);
        // Product variant in category A from itself, and in category B from its parent
        $this->givenAProductVariantWithPerfectEnrichmentAndImage($productModelB, [new SetCategories(['category_A'])]);
        // Product variant in only category B
        $this->givenAProductVariantWithPerfectEnrichmentButWithoutAttributeImage($productModelB, [new SetCategories(['category_B'])]);

        $goodEnrichment = new KeyIndicatorCode('good_enrichment');
        $hasImage = new KeyIndicatorCode('has_image');

        $expectedProductsKeyIndicators = [
            'good_enrichment' => new KeyIndicator($goodEnrichment, 3, 2),
            'has_image' => new KeyIndicator($hasImage, 2, 3),
        ];

        $expectedProductModelsKeyIndicators = [
            'good_enrichment' => new KeyIndicator($goodEnrichment, 3, 2),
            'has_image' => new KeyIndicator($hasImage, 2, 3),
        ];

        $productsKeyIndicators = $this->get('akeneo.pim.automation.data_quality_insights.infrastructure.elasticsearch.query.get_product_key_indicators_query')
            ->byCategory(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new CategoryCode('category_A'), $goodEnrichment, $hasImage);
        $productModelsKeyIndicators = $this->get('akeneo.pim.automation.data_quality_insights.infrastructure.elasticsearch.query.get_product_key_indicators_query')
            ->byCategory(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new CategoryCode('category_A'), $goodEnrichment, $hasImage);

        $this->assertEquals($expectedProductsKeyIndicators, $productsKeyIndicators);
        $this->assertEquals($expectedProductModelsKeyIndicators, $productModelsKeyIndicators);
    }

    private function givenAProductWithoutValues(array $data = []): void
    {
        $product = $this->createProduct($this->getRandomCode(), $data);
        $this->updateKeyIndicators($product->getUuid(), false, false);
    }

    private function givenAProductWithPerfectEnrichmentAndImage(array $userIntents = []): void
    {
        $product = $this->createProduct($this->getRandomCode(), $userIntents);
        $this->updateKeyIndicators($product->getUuid(), true, true);
    }

    private function givenAProductWithPerfectEnrichmentButWithoutAttributeImage(array $userIntents = []): void
    {
        $product = $this->createProduct($this->getRandomCode(), $userIntents);
        $this->updateKeyIndicators($product->getUuid(), true, false);
    }

    private function givenAProductWithImageButMissingEnrichment(array $userIntents = []): void
    {
        $product = $this->createProduct($this->getRandomCode(), $userIntents);
        $this->updateKeyIndicators($product->getUuid(), false, true);
    }

    private function givenAProductVariantWithoutValues(ProductModelInterface $productModel, array $userIntents = []): void
    {
        $productVariant = $this->createMinimalProductVariant(
            $this->getRandomCode(),
            $productModel->getCode(),
            DataQualityInsightsTestCase::MINIMAL_VARIANT_OPTIONS[0],
            $userIntents
        );

        $this->updateKeyIndicators($productModel->getId(), false, false, true);
        $this->updateKeyIndicators($productVariant->getUuid(), false, false);
    }

    private function givenAProductVariantWithPerfectEnrichmentAndImage(ProductModelInterface $productModel, array $data = []): void
    {
        $productVariant = $this->createMinimalProductVariant(
            $this->getRandomCode(),
            $productModel->getCode(),
            DataQualityInsightsTestCase::MINIMAL_VARIANT_OPTIONS[1],
            $data
        );

        $this->updateKeyIndicators($productModel->getId(), true, true, true);
        $this->updateKeyIndicators($productVariant->getUuid(), true, true);
    }

    private function givenAProductVariantWithPerfectEnrichmentButWithoutAttributeImage(ProductModelInterface $productModel, array $userIntents = []): void
    {
        $productVariant = $this->createMinimalProductVariant(
            $this->getRandomCode(),
            $productModel->getCode(),
            DataQualityInsightsTestCase::MINIMAL_VARIANT_OPTIONS[2],
            $userIntents
        );

        $this->updateKeyIndicators($productModel->getId(), true, false, true);
        $this->updateKeyIndicators($productVariant->getUuid(), true, false);
    }

    private function updateKeyIndicators(int|UuidInterface $productId, bool $goodEnrichment, bool $hasImage, bool $isProductModel = false): void
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

        $updatedId = $isProductModel ? sprintf('product_model_%d', $productId) : sprintf('product_%s', $productId->toString());
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'source' => "ctx._source.rates = params.rates; ctx._source.data_quality_insights = params.data_quality_insights;",
                    'params' => [
                        'rates' => [],
                        'data_quality_insights' => ['key_indicators' => $keyIndicators],
                    ],
                ],
                'query' => [
                    'term' => [
                        'id' => $updatedId,
                    ],
                ],
            ]
        );

        $this->esClient->refreshIndex();
    }
}
