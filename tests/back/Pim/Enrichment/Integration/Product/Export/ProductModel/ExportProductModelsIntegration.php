<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductModel;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

/**
 * @group ce
 */
class ExportProductModelsIntegration extends AbstractExportTestCase
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
            'code'        => 'variation_name',
            'type'        => 'pim_catalog_text',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createAttribute([
            'code'        => 'variation_image',
            'type'        => 'pim_catalog_image',
            'group'       => 'attributeGroupA',
            'localizable' => false,
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
        $this->createAttribute([
            'code'        => 'ean',
            'type'        => 'pim_catalog_text',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createFamily([
            'code'        => 'clothing',
            'attributes'  => ['sku', 'name', 'variation_name', 'variation_image', 'size', 'ean', 'sku', 'color'],
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
                    'attributes' => ['color', 'variation_name', 'variation_image'],
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
    }

    public function testProductModelsExport()
    {
        $expectedCsv = <<<CSV
code;family_variant;parent;categories;color;name-en_US;variation_image;variation_name
apollon;clothing_color_size;;tshirt;;;;
apollon_blue;clothing_color_size;apollon;summer,tshirt,v-neck;blue;;;"my blue tshirt"
apollon_pink;clothing_color_size;apollon;round-neck,tshirt;pink;;;"my pink tshirt"

CSV;
        $this->assertProductModelExport($expectedCsv, []);
    }
}
