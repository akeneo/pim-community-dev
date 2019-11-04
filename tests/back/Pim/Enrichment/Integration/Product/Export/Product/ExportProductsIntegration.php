<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\Product;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

/**
 * @group ce
 */
class ExportProductsIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createAttribute([
            'code'        => 'name',
            'type'        => 'pim_catalog_textarea',
            'group'       => 'attributeGroupA',
            'localizable' => true,
            'scopable'    => false,
        ]);
        $this->createAttribute([
            'code'        => 'color',
            'type'        => 'pim_catalog_simpleselect',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createAttributeOption([
            'code'        => 'blue',
            'attribute'   => 'color',
        ]);
        $this->createAttributeOption([
            'code'        => 'pink',
            'attribute'   => 'color',
        ]);
        $this->createAttribute([
            'code'        => 'size',
            'type'        => 'pim_catalog_simpleselect',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createAttributeOption([
            'code'        => 'm',
            'attribute'   => 'size',
        ]);
        $this->createAttributeOption([
            'code'        => 'l',
            'attribute'   => 'size',
        ]);
        $this->createAttributeOption([
            'code'        => 'xl',
            'attribute'   => 'size',
        ]);
        $this->createAttribute([
            'code'        => 'ean',
            'type'        => 'pim_catalog_text',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createAttribute([
            'code'        => 'variation_name',
            'type'        => 'pim_catalog_text',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createFamily([
            'code'        => 'clothing',
            'attributes'  => ['sku', 'name', 'color', 'variation_name', 'size', 'ean'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'name']
            ]
        ]);
        $this->createFamilyVariant([
            'code'        => 'clothing_color_size',
            'family'      => 'clothing',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['color', 'variation_name'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => ['size', 'ean', 'sku'],
                ],
            ]
        ]);

        $this->createCategory(['code' => 'tshirt']);
        $this->createCategory(['code' => 'summer', 'parent' => 'tshirt']);
        $this->createCategory(['code' => 'spring', 'parent' => 'tshirt']);
        $this->createCategory(['code' => 'long-sleeves', 'parent' => 'tshirt']);
        $this->createCategory(['code' => 'v-neck', 'parent' => 'long-sleeves']);
        $this->createCategory(['code' => 'round-neck', 'parent' => 'long-sleeves']);

        $this->createProductModel(
            [
                'code' => 'apollon',
                'family_variant' => 'clothing_color_size',
                'categories' => ['tshirt'],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'apollon_blue',
                'family_variant' => 'clothing_color_size',
                'parent' => 'apollon',
                'categories' => ['v-neck', 'summer'],
                'values'  => [
                    'color'  => [['data' => 'blue', 'locale' => null, 'scope' => null]],
                    'variation_name'  => [['data' => 'my blue tshirt', 'locale' => null, 'scope' => null]],
                ]
            ]
        );
        $this->createProductModel(
            [
                'code' => 'apollon_pink',
                'family_variant' => 'clothing_color_size',
                'parent' => 'apollon',
                'categories' => ['round-neck', 'tshirt'],
                'values'  => [
                    'color'  => [['data' => 'pink', 'locale' => null, 'scope' => null]],
                    'variation_name'  => [['data' => 'my pink tshirt', 'locale' => null, 'scope' => null]],
                ]
            ]
        );
        $this->createVariantProduct(
            'apollon_pink_m',
            [
                'family' => 'clothing',
                'parent' => 'apollon_pink',
                'categories' => ['spring'],
                'values'  => [
                    'size'  => [['data' => 'm', 'locale' => null, 'scope' => null]],
                    'ean'  => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                ]
            ]
        );
        $this->createVariantProduct(
            'apollon_pink_l',
            [
                'family' => 'clothing',
                'parent' => 'apollon_pink',
                'values'  => [
                    'size'  => [['data' => 'l', 'locale' => null, 'scope' => null]],
                    'ean'  => [['data' => '12345679', 'locale' => null, 'scope' => null]],
                ]
            ]
        );
        $this->createVariantProduct(
            'apollon_pink_xl',
            [
                'family' => 'clothing',
                'parent' => 'apollon_pink',
                'categories' => ['tshirt','summer'],
                'values'  => [
                    'size'  => [['data' => 'xl', 'locale' => null, 'scope' => null]],
                    'ean'  => [['data' => '12345465', 'locale' => null, 'scope' => null]],
                ]
            ]
        );
    }

    public function testVariantProductExport()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;parent;groups;color;ean;name-en_US;size;variation_name
apollon_pink_m;round-neck,spring,tshirt;1;clothing;apollon_pink;;pink;12345678;;m;"my pink tshirt"
apollon_pink_l;round-neck,tshirt;1;clothing;apollon_pink;;pink;12345679;;l;"my pink tshirt"
apollon_pink_xl;round-neck,summer,tshirt;1;clothing;apollon_pink;;pink;12345465;;xl;"my pink tshirt"

CSV;

        $this->assertProductExport($expectedCsv, []);
    }
}
