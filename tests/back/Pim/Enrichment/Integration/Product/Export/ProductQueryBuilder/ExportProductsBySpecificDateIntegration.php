<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsBySpecificDateIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [new SetFamily('familyA2')]);

        $this->createProduct('product_2', [new SetFamily('familyA2')]);

        $this->createProduct('product_3', [new SetFamily('familyA2')]);
    }

    public function testProductExportByFilteringOnDateSinceLastJob()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_3');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_metric;a_metric-unit;a_number_float
%s;product_1;;1;familyA2;;;;
%s;product_2;;1;familyA2;;;;
%s;product_3;;1;familyA2;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'updated',
                        'operator' => 'SINCE LAST JOB',
                        'value'    => 'csv_product_export',
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport(\sprintf(
            $expectedCsv,
            $product1->getUuid()->toString(),
            $product2->getUuid()->toString(),
            $product3->getUuid()->toString(),
        ), $config);

        // wait before updating the product in order to have updated_date > SINCE LAST JOB
        sleep(2);

        $this->updateProduct('product_3', ['family' => 'familyA1']);

        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_date;a_file;a_localizable_image-en_US
%s;product_3;;1;familyA1;;;;

CSV;

        $csv = $this->jobLauncher->launchSubProcessExport('csv_product_export', null, $config);

        $this->assertSame(\sprintf($expectedCsv, $product3->getUuid()->toString()), $csv);
    }

    public function testProductExportByFilteringSinceASpecificDate()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_3');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_metric;a_metric-unit;a_number_float
%s;product_1;;1;familyA2;;;;
%s;product_2;;1;familyA2;;;;
%s;product_3;;1;familyA2;;;;

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

        $this->assertProductExport(\sprintf(
            $expectedCsv,
            $product1->getUuid()->toString(),
            $product2->getUuid()->toString(),
            $product3->getUuid()->toString(),
        ), $config);
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
