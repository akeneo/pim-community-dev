<?php

namespace Pim\Bundle\ConnectorBundle\tests\integration\Export\ProductModel;

use Pim\Bundle\ConnectorBundle\tests\integration\Export\AbstractExportTestCase;

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
        $this->createFamily([
            'code'        => 'clothing',
            'attributes'  => ['sku', 'name'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'name']
            ]
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

        $this->createProductModel(
            [
                'code' => 'apollon',
                'family_variant' => 'clothing_color_size',
            ]
        );
        $this->createProductModel(
            [
                'code' => 'apollon_blue',
                'family_variant' => 'clothing_color_size',
                'parent' => 'apollon',
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
    }

    public function testProductModelsExport()
    {
        $expectedCsv = <<<CSV
code;family_variant;parent;categories;color;name-de_DE;name-en_US;name-fr_FR;name-zh_CN;variation_image;variation_name
apollon;clothing_color_size;;;;;;;;;
apollon_blue;clothing_color_size;apollon;;blue;;;;;;"my blue tshirt"
apollon_pink;clothing_color_size;apollon;;pink;;;;;;"my pink tshirt"

CSV;

        $this->assertProductModelExport($expectedCsv, []);
    }
}
