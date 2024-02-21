<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\StandardFormat;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertStandardFormatIntoUserIntentsHandlerIntegration extends EnrichmentProductTestCase
{
    /** @test */
    public function it_converts_into_user_intents(): void
    {
        $envelope = $this->get('pim_enrich.product.query_message_bus')->dispatch(new GetUserIntentsFromStandardFormat([
            'family' => 'accessories',
            'associations' => [
                'PACK' => [
                    'groups' => ['RELATED'],
                    'products' => ['associated_product'],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => ['associated_product_model'],
                ],
            ],
            'quantified_associations' => [
                'bundle' => [
                    'products' => [['identifier' => 'associated_product', 'quantity' => 12]],
                    'product_models' => [['identifier' => 'associated_product_model', 'quantity' => 21]],
                ]
            ],
            'values' => [
                'name' => [['data' => 'Bonjour', 'scope' => null, 'locale' => null]],
                'measurement' => [['data' => ['amount' => 10, 'unit' => 'KILOGRAM'], 'scope' => 'e-commerce', 'locale' => 'fr_FR']]
            ]
        ]));

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);

        Assert::assertEqualsCanonicalizing([
            new SetFamily('accessories'),
            new ReplaceAssociatedProducts('PACK', ['associated_product']),
            new ReplaceAssociatedProductModels('PACK', []),
            new ReplaceAssociatedGroups('PACK', ['RELATED']),
            new ReplaceAssociatedProducts('SUBSTITUTION', []),
            new ReplaceAssociatedProductModels('SUBSTITUTION', ['associated_product_model']),
            new ReplaceAssociatedGroups('SUBSTITUTION', []),
            new ReplaceAssociatedQuantifiedProducts('bundle', [
                new QuantifiedEntity('associated_product', 12)
            ]),
            new ReplaceAssociatedQuantifiedProductModels('bundle', [
                new QuantifiedEntity('associated_product_model', 21)
            ]),
            new SetTextValue('name', null, null, 'Bonjour'),
            new SetMeasurementValue('measurement', 'e-commerce', 'fr_FR', 10, 'KILOGRAM'),
        ], $handledStamp->getResult());
    }

    /** @test */
    public function it_converts_null_value_into_clear_user_intents(): void
    {
        $envelope = $this->get('pim_enrich.product.query_message_bus')->dispatch(new GetUserIntentsFromStandardFormat([
            'values' => [
                'name' => [['data' => null, 'scope' => null, 'locale' => null]],
                'measurement' => [['data' => ['amount' => null, 'unit' => 'KILOGRAM'], 'scope' => null, 'locale' => null]]
            ]
        ]));

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::assertEqualsCanonicalizing([
            new ClearValue('name', null, null),
            new ClearValue('measurement', null, null),
        ], $handledStamp->getResult());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadEnrichmentProductFunctionalFixtures();
        $this->createProductModel('associated_product_model', 'color_variant_accessories', []);
        $this->createProduct('associated_product', []);

        $this->get('akeneo_measure.persistence.measurement_family_repository')->save(
            MeasurementFamily::create(
                MeasurementFamilyCode::fromString('weight'),
                LabelCollection::fromArray([]),
                UnitCode::fromString('KILOGRAM'),
                [
                    Unit::create(
                        UnitCode::fromString('KILOGRAM'),
                        LabelCollection::fromArray([]),
                        [Operation::create("mul", "1")],
                        "km",
                    )
                ]
            )
        );
        $this->createAttribute('measurement', [
            'type' => AttributeTypes::METRIC,
            'metric_family' =>'weight',
            'default_metric_unit' => 'KILOGRAM',
            'decimals_allowed'    => true,
            'negative_allowed'    => true,
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
