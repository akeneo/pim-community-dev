<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsWithAttributesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code'        => 'my_family',
            'attributes'  => ['a_text', 'a_text_area'],
            'attribute_requirements' => [
                'tablet' => ['a_text', 'a_text_area']
            ]

        ]);

        $this->createProduct('product_1', [
            new SetFamily('my_family'),
            new SetTextValue('a_text', null, null, 'Awesome'),
            new SetTextareaValue('a_text_area', null, null, 'Amazing')
        ]);

        $this->createProduct('product_2', [
            new SetFamily('my_family'),
            new SetTextValue('a_text', null, null, 'Awesome product'),
        ]);

        $this->createProduct('product_4');
    }

    public function testProductExportBySelectingOnlyOneAttribute(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_4');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_text_area
{$product1->getUuid()->toString()};product_1;;1;my_family;;Amazing
{$product2->getUuid()->toString()};product_2;;1;my_family;;
{$product3->getUuid()->toString()};product_4;;1;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                    'attributes'=> ['a_text_area'],
                ],
            ],
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv,$config);
    }

    public function testProductExportWithAttributesInTheSameOrderAsTheFilter(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_4');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_text_area;a_text
{$product1->getUuid()->toString()};product_1;;1;my_family;;Amazing;Awesome
{$product2->getUuid()->toString()};product_2;;1;my_family;;;"Awesome product"
{$product3->getUuid()->toString()};product_4;;1;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                    'attributes'=> ['a_text_area', 'a_text'],
                ],
            ],
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);

        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_text;a_text_area
{$product1->getUuid()->toString()};product_1;;1;my_family;;Awesome;Amazing
{$product2->getUuid()->toString()};product_2;;1;my_family;;"Awesome product";
{$product3->getUuid()->toString()};product_4;;1;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                    'attributes'=> ['a_text', 'a_text_area'],
                ],
            ],
            'with_uuid' => true,
        ];

        $csv = $this->jobLauncher->launchSubProcessExport('csv_product_export', null, $config);

        $this->assertSame($expectedCsv, $csv);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-5994
     */
    public function testProductExportByExportingTheAttributesOnlyOnce(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_text_area;a_text
{$product1->getUuid()->toString()};product_1;;1;my_family;;Amazing;Awesome
{$product2->getUuid()->toString()};product_2;;1;my_family;;;"Awesome product"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'family',
                        'operator' => 'IN',
                        'value'    => ['my_family']
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                    'attributes'=> ['a_text_area', 'a_text'],
                ],
            ],
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
