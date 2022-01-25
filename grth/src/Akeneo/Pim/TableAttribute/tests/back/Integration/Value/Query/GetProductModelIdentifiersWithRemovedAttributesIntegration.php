<?php

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use PHPUnit\Framework\Assert;

final class GetProductModelIdentifiersWithRemovedAttributesIntegration extends TestCase
{
    use EntityBuilderTrait;

    private GetProductModelIdentifiersWithRemovedAttributeInterface $query;

    /** @test */
    public function it_gets_product_model_identifiers_with_removed_attributes(): void
    {
        Assert::assertSame([['model3', 'model4']], \iterator_to_array($this->query->nextBatch(['a_text_area'], 100)));
    }

    /** @test */
    public function it_gets_product_model_identifiers_with_removed_table_attributes(): void
    {
        Assert::assertSame(
            [['model1', 'model3']],
            \iterator_to_array($this->query->nextBatch(['nutrition', 'nutrition2'], 100))
        );
    }

    /** @test */
    public function it_gets_product_model_identifiers_with_removed_attributes_with_any_attribute(): void
    {
        Assert::assertSame(
            [['model1', 'model3', 'model4']],
            \iterator_to_array($this->query->nextBatch(['a_text_area', 'nutrition', 'nutrition2'], 100))
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $nutrition = $this->createAttribute([
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                ],
            ],
        ]);
        $nutrition2 = $this->createAttribute([
            'code' => 'nutrition2',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                ],
            ],
        ]);

        /** @var FamilyInterface $familyA */
        $familyA = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA');
        $familyA->addAttribute($nutrition);
        $familyA->addAttribute($nutrition2);
        $this->get('pim_catalog.saver.family')->save($familyA);

        $this->createProductModel(
            [
                'code' => 'model1',
                'family_variant' => 'familyVariantA2',
                'values' => [
                    'nutrition2' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [['ingredient' => 'butter', 'quantity' => 20]],
                        ],
                    ],
                ],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'model2',
                'family_variant' => 'familyVariantA2',
                'values' => [],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'model3',
                'family_variant' => 'familyVariantA2',
                'values' => [
                    'nutrition' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [['ingredient' => 'butter', 'quantity' => 20]],
                        ],
                    ],
                    'a_text_area' => [['locale' => null, 'scope' => null, 'data' => 'foo']],
                ],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'model4',
                'family_variant' => 'familyVariantA2',
                'values' => [
                    'a_text_area' => [['locale' => null, 'scope' => null, 'data' => 'foo']],
                ],
            ]
        );

        $this->query = $this->get(
            'akeneo.pim.enrichment.product.query.get_product_model_identifiers_with_removed_attribute'
        );
    }
}
