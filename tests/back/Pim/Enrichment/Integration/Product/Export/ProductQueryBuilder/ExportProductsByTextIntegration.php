<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByTextIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_text']
        ]);

        $this->createProduct('product_1', [
            new SetFamily('a_family'),
            new SetTextValue('a_text', null, null, 'Awesome')
        ]);

        $this->createProduct('product_2', [
            new SetFamily('a_family'),
            new SetTextValue('a_text', null, null, 'Awesome product')
        ]);

        $this->createProduct('product_3', [
            new SetFamily('a_family'),
            new SetTextValue('a_text', null, null, 'This is nice')
        ]);

        $this->createProduct('product_4', [new SetFamily('a_family')]);
    }

    public function testProductExportByFilteringWithEqualsOperatorOnText()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_text
%s;product_1;;1;a_family;;Awesome

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text',
                        'operator' => '=',
                        'value'    => 'Awesome'
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport(\sprintf($expectedCsv, $product1->getUuid()->toString()), $config);
    }

    public function testProductExportByFilteringWithContainsOperatorOnText()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_text
%s;product_1;;1;a_family;;Awesome
%s;product_2;;1;a_family;;"Awesome product"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text',
                        'operator' => 'CONTAINS',
                        'value'    => 'Awesome'
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport(\sprintf($expectedCsv, $product1->getUuid()->toString(), $product2->getUuid()->toString()), $config);
    }

    public function testProductExportByFilteringWithStartWithOperatorOnText()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_text
%s;product_1;;1;a_family;;Awesome
%s;product_2;;1;a_family;;"Awesome product"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text',
                        'operator' => 'STARTS WITH',
                        'value'    => 'Aw'
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport(\sprintf($expectedCsv, $product1->getUuid()->toString(), $product2->getUuid()->toString()), $config);
    }

    public function testProductExportByFilteringWithIsEmptyOperatorOnText()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_4');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_text
%s;product_4;;1;a_family;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text',
                        'operator' => 'EMPTY',
                        'value'    => 'Aw'
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport(\sprintf($expectedCsv, $product1->getUuid()->toString()), $config);
    }
}
