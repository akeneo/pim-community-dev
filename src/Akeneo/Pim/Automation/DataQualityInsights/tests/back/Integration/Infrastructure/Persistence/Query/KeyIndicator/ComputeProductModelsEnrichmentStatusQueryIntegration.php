<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductModelsEnrichmentStatusQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_computes_the_enrichment_status_of_product_models()
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createChannel('mobile', ['locales' => ['en_US']]);

        $this->createAttribute('name', ['scopable' => true, 'localizable' => true]);
        $this->createAttribute('description', ['scopable' => true, 'localizable' => true]);
        $this->createAttribute('image', ['scopable' => true, 'localizable' => false]);

        $this->createSimpleSelectAttributeWithOptions('color', ['red', 'blue']);
        $this->createSimpleSelectAttributeWithOptions('size', ['XL', 'M', 'L']);

        $this->createFamily('a_family', [
            'attributes' => ['name', 'description', 'color', 'size', 'image'],
            'attribute_requirements' => [
                'ecommerce' => ['name', 'image'],
                'mobile' => ['name'],
            ],
        ]);
        $this->createFamilyVariant('a_family_variant', 'a_family', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['image'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => ['sku'],
                ],
            ],
        ]);

        $productModelId = $this->createProductModel('a_product_model', 'a_family_variant', [
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'A product model'],
                    ['scope' => 'mobile', 'locale' => 'en_US', 'data' => 'A product model'],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'Un produit modèle'],
                ],
                'description' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'Whatever the description'],
                    ['scope' => 'mobile', 'locale' => 'en_US', 'data' => 'Whatever'],
                ],
            ]
        ])->getId();

        $subProductModelId = $this->createSubProductModel('a_sub_product_model', 'a_family_variant', 'a_product_model', [
            'values' => [
                'color' => [
                    ['scope' => null, 'locale' => null, 'data' => 'red']
                ],
                'image' => [
                    ['scope' => 'mobile', 'locale' => null, 'data' => 'red-ziggy.png']
                ]
            ]
        ])->getId();

        $expectedEnrichmentStatus = [
            $productModelId => [
                'ecommerce' => [
                    'en_US' => true,
                    'fr_FR' => false,
                ],
                'mobile' => [
                    'en_US' => true,
                ]
            ],
            $subProductModelId => [
                'ecommerce' => [
                    'en_US' => false,
                    'fr_FR' => false,
                ],
                'mobile' => [
                    'en_US' => true,
                ]
            ]
        ];

        $productModelIds = $this->get(ProductModelIdFactory::class)->createCollection([(string)$productModelId, (string)$subProductModelId]);
        ($this->get(EvaluateProductModels::class))($productModelIds);

        $productModelsEnrichmentStatus = $this->get('akeneo.pim.automation.data_quality_insights.query.compute_product_models_enrichment_status_query')
            ->compute($productModelIds);

        $this->assertEqualsCanonicalizing($expectedEnrichmentStatus, $productModelsEnrichmentStatus);
    }
}
