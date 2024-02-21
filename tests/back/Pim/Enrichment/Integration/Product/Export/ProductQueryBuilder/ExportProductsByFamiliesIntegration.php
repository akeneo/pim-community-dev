<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByFamiliesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [new SetFamily('familyA')]);
        $this->createProduct('product_2', [new SetFamily('familyA1')]);
        $this->createProduct('product_3', [new SetFamily('familyA2')]);
        $this->createProduct('product_4');
    }

    public function testProductExportWithFilterOnOneFamily(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;an_image;a_date;a_file;a_localizable_image-en_US;a_localized_and_scopable_text_area-en_US-tablet;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD;a_simple_select;a_text;a_text_area;a_yes_no
{$product1->getUuid()->toString()};product_1;;1;familyA;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'family',
                        'operator' => 'IN',
                        'value'    => ['familyA']
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithFilterOnAListOfFamilies(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;an_image;a_date;a_file;a_localizable_image-en_US;a_localized_and_scopable_text_area-en_US-tablet;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD;a_simple_select;a_text;a_text_area;a_yes_no
{$product1->getUuid()->toString()};product_1;;1;familyA;;;;;;;;;;;;;;;;;;;;;;
{$product2->getUuid()->toString()};product_2;;1;familyA1;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'family',
                        'operator' => 'IN',
                        'value'    => ['familyA', 'familyA1']
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithoutAnyFilterOnFamily(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_3');
        $product4 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_4');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;an_image;a_date;a_file;a_localizable_image-en_US;a_localized_and_scopable_text_area-en_US-tablet;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD;a_simple_select;a_text;a_text_area;a_yes_no
{$product1->getUuid()->toString()};product_1;;1;familyA;;;;;;;;;;;;;;;;;;;;;;
{$product2->getUuid()->toString()};product_2;;1;familyA1;;;;;;;;;;;;;;;;;;;;;;
{$product3->getUuid()->toString()};product_3;;1;familyA2;;;;;;;;;;;;;;;;;;;;;;
{$product4->getUuid()->toString()};product_4;;1;;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
