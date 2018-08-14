<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsBySpecificDateIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', ['family' => 'familyA2']);

        $this->createProduct('product_2', ['family' => 'familyA2']);

        $this->createProduct('product_3', ['family' => 'familyA2']);
    }

    public function testProductExportByFilteringOnDateSinceLastJob()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_metric;a_metric-unit;a_number_float
product_1;;1;familyA2;;;;
product_2;;1;familyA2;;;;
product_3;;1;familyA2;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'updated',
                        'operator' => 'SINCE LAST JOB',
                        'value'    => 'csv_product_export'
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);

        // wait before updating the product in order to have updated_date > SINCE LAST JOB
        sleep(2);

        $this->updateProduct('product_3', ['family' => 'familyA1']);

        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_date;a_file;a_localizable_image-en_US
product_3;;1;familyA1;;;;

CSV;

        $csv = $this->jobLauncher->launchSubProcessExport('csv_product_export', null, $config);

        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductExportByFilteringSinceASpecificDate()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_metric;a_metric-unit;a_number_float
product_1;;1;familyA2;;;;
product_2;;1;familyA2;;;;
product_3;;1;familyA2;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'updated',
                        'operator' => '>',
                        'value'    => '2016-04-25 00:00:00'
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportByFilteringUntilASpecificDate()
    {
        $expectedCsv = '';

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'updated',
                        'operator' => '<',
                        'value'    => '2016-04-25 00:00:00'
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
