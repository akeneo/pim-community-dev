<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByLocalesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createAttribute([
            'code'              => 'name',
            'group'             => 'attributeGroupA',
            'type'              => 'pim_catalog_textarea',
            'available_locales' => ['fr_FR', 'en_US'],
            'localizable'       => true,
            'scopable'          => false,
            'labels'            => [
                'fr_FR' => 'French name',
                'en_US' => 'English label',
            ],
        ]);

        $this->createAttribute([
            'code'              => 'description',
            'group'             => 'attributeGroupA',
            'type'              => 'pim_catalog_textarea',
            'available_locales' => ['fr_FR'],
            'localizable'       => true,
            'scopable'          => false,
            'labels'            => [
                'fr_FR' => 'French name',
                'en_US' => 'English label',
            ],
        ]);

        $this->createFamily([
            'code'       => 'localized',
            'attributes' => ['sku', 'name'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'name']
            ]

        ]);

        $this->createProduct('french', [
            'family' => 'localized',
            'values' => [
                'name'        => [
                    ['data' => 'French name', 'locale' => 'fr_FR', 'scope' => null],
                ],
                'description' => [
                    ['data' => 'French desc', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('english', [
            'family' => 'localized',
            'values' => [
                'name'        => [
                    ['data' => 'English name', 'locale' => 'en_US', 'scope' => null],
                ],
                'description' => [
                    ['data' => 'French desc', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('complete', [
            'family' => 'localized',
            'values' => [
                'name'        => [
                    ['data' => 'French name', 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => 'English name', 'locale' => 'en_US', 'scope' => null],
                ],
                'description' => [
                    ['data' => 'French desc', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('empty', ['family' => 'localized']);
    }

    public function testProductExportWithFrenchData()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;description-fr_FR;name-fr_FR
french;;1;localized;;"French desc";"French name"
english;;1;localized;;"French desc";
complete;;1;localized;;"French desc";"French name"
empty;;1;localized;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['fr_FR'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithEnglishAndFrenchData()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;description-fr_FR;name-en_US;name-fr_FR
french;;1;localized;;"French desc";;"French name"
english;;1;localized;;"French desc";"English name";
complete;;1;localized;;"French desc";"English name";"French name"
empty;;1;localized;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportAfterRemovingFrenchLocaleFromTabletChannel()
    {
        $channel = $this->get('pim_api.repository.channel')->findOneByIdentifier('tablet');
        $this->get('pim_catalog.updater.channel')->update($channel, ['locales' => ['en_US']]);
        $this->get('pim_catalog.saver.channel')->save($channel);

        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;name-en_US
english;;1;localized;;"English name"
complete;;1;localized;;"English name"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'completeness',
                        'operator' => '=',
                        'value'    => '100'
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
