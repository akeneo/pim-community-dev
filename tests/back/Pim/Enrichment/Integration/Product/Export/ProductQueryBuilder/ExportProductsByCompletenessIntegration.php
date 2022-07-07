<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByCompletenessIntegration extends AbstractExportTestCase
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
            'code'        => 'localized',
            'attributes'  => ['sku', 'name'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'name']
            ]

        ]);

        $this->createProduct('french', [
            new SetFamily('localized'),
            new SetTextValue('name', null, 'fr_FR', 'French name')
        ]);

        $this->createProduct('english', [
            new SetFamily('localized'),
            new SetTextValue('name', null, 'en_US', 'English name')
        ]);

        $this->createProduct('complete', [
            new SetFamily('localized'),
            new SetTextValue('name', null, 'fr_FR', 'French complete'),
            new SetTextValue('name', null, 'en_US', 'English complete')
        ]);

        $this->createProduct('empty', [new SetFamily('localized')]);
    }

    public function testProductExportWithCompleteProductsOnAtLeastOneLocale()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('complete');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('english');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('french');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;name-en_US
%s;complete;;1;localized;;"English complete"
%s;english;;1;localized;;"English name"
%s;french;;1;localized;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'completeness',
                        'operator' => '=',
                        'value'    => '100',
                        'context'  => [
                            'locales' => ['fr_FR', 'en_US']
                        ]
                    ],
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

    public function testProductExportWithCompleteProductsOnAllLocales()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('complete');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;name-en_US
%s;complete;;1;localized;;"English complete"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'completeness',
                        'operator' => 'GREATER OR EQUALS THAN ON ALL LOCALES',
                        'value'    => '100',
                        'context'  => [
                            'locales' => ['fr_FR', 'en_US']
                        ]
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport(\sprintf($expectedCsv, $product1->getUuid()->toString()), $config);
    }

    public function testProductExportWithIncompleteProductsOnAllLocales()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('empty');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;name-en_US
%s;empty;;1;localized;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'completeness',
                        'operator' => 'LOWER THAN ON ALL LOCALES',
                        'value'    => '100',
                        'context'  => [
                            'locales' => ['fr_FR', 'en_US']
                        ]
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport(\sprintf($expectedCsv, $product1->getUuid()->toString()), $config);
    }

    public function testProductExportWithoutFilterOnCompleteness()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('complete');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('empty');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('english');
        $product4 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('french');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;name-en_US
%s;complete;;1;localized;;"English complete"
%s;empty;;1;localized;;
%s;english;;1;localized;;"English name"
%s;french;;1;localized;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
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
            $product4->getUuid()->toString(),
        ), $config);
    }
}
