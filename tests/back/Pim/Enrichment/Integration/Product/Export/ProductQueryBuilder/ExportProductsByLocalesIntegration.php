<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
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

        $this->createAttribute([
            'code'              => 'localeSpecificAttribute',
            'group'             => 'attributeGroupA',
            'type'              => 'pim_catalog_textarea',
            'available_locales' => ['en_US'],
            'localizable'       => false,
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

        $this->createFamily([
            'code'       => 'accessories',
            'attributes' => ['name', 'localeSpecificAttribute']
        ]);

        $this->createProduct('french', [
            new SetFamily('localized'),
            new SetTextValue('name', null, 'fr_FR', 'French name'),
            new SetTextareaValue('description', null, 'fr_FR', 'French desc')
        ]);

        $this->createProduct('english', [
            new SetFamily('localized'),
            new SetTextValue('name', null, 'en_US', 'English name'),
            new SetTextareaValue('description', null, 'fr_FR', 'French desc')
        ]);

        $this->createProduct('complete', [
            new SetFamily('localized'),
            new SetTextValue('name', null, 'fr_FR', 'French name'),
            new SetTextValue('name', null, 'en_US', 'English name'),
            new SetTextareaValue('description', null, 'fr_FR', 'French desc'),
        ]);

        $this->createProduct('empty', [new SetFamily('localized')]);

        $this->createProduct('withLocaleSpecificAttribute', [
            new SetFamily('accessories'),
            new SetTextValue('name', null, 'en_US', 'English name'),
            new SetTextareaValue('localeSpecificAttribute', null, null, 'Locale Specific Value')
        ]);
    }

    public function testProductExportWithFrenchData(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('complete');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('empty');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('english');
        $product4 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('french');
        $product5 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('withLocaleSpecificAttribute');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;description-fr_FR;name-fr_FR
{$product1->getUuid()->toString()};complete;;1;localized;;"French desc";"French name"
{$product2->getUuid()->toString()};empty;;1;localized;;;
{$product3->getUuid()->toString()};english;;1;localized;;"French desc";
{$product4->getUuid()->toString()};french;;1;localized;;"French desc";"French name"
{$product5->getUuid()->toString()};withLocaleSpecificAttribute;;1;accessories;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['fr_FR'],
                ],
            ],
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithEnglishAndFrenchData(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('complete');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('empty');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('english');
        $product4 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('french');
        $product5 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('withLocaleSpecificAttribute');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;description-fr_FR;localeSpecificAttribute;name-en_US;name-fr_FR
{$product1->getUuid()->toString()};complete;;1;localized;;"French desc";;"English name";"French name"
{$product2->getUuid()->toString()};empty;;1;localized;;;;;
{$product3->getUuid()->toString()};english;;1;localized;;"French desc";;"English name";
{$product4->getUuid()->toString()};french;;1;localized;;"French desc";;;"French name"
{$product5->getUuid()->toString()};withLocaleSpecificAttribute;;1;accessories;;;"Locale Specific Value";"English name";

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR'],
                ],
            ],
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportAfterRemovingFrenchLocaleFromTabletChannel(): void
    {
        $channel = $this->get('pim_api.repository.channel')->findOneByIdentifier('tablet');
        $this->get('pim_catalog.updater.channel')->update($channel, ['locales' => ['en_US']]);
        $this->get('pim_catalog.saver.channel')->save($channel);

        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('complete');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('english');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('withLocaleSpecificAttribute');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;localeSpecificAttribute;name-en_US
{$product1->getUuid()->toString()};complete;;1;localized;;;"English name"
{$product2->getUuid()->toString()};english;;1;localized;;;"English name"
{$product3->getUuid()->toString()};withLocaleSpecificAttribute;;1;accessories;;"Locale Specific Value";"English name"

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
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
