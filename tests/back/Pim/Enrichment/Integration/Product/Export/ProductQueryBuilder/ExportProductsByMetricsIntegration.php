<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByMetricsIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            new SetMeasurementValue('a_metric_without_decimal_negative', null, null, -10, 'CELSIUS'),
        ]);

        $this->createProduct('product_2', [
            new SetMeasurementValue('a_metric_without_decimal_negative', null, null, 20, 'CELSIUS'),
        ]);

        $this->createProduct('product_3');
    }

    public function testProductExportByFilteringOnMetric(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_metric_without_decimal_negative;a_metric_without_decimal_negative-unit
{$product1->getUuid()->toString()};product_1;;1;;;-10;CELSIUS

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_metric_without_decimal_negative',
                        'operator' => '<',
                        'value'    => ['amount' => 15, 'unit' => 'CELSIUS']
                    ]
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
}
