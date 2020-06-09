<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Enrichment\ReferenceEntity\Integration\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ReferenceEntityAttributeCopierIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_copies_a_reference_entity_single_link_value()
    {
        $singleAttribute = $this->createAttribute([
            'code' => 'designer',
            'group' => 'other',
            'scopable' => false,
            'localizable' => true,
            'type' => ReferenceEntityType::REFERENCE_ENTITY,
            'reference_data_name' => 'designers',
        ]);

        $product = $this->createProduct([
            'values' => [
                'designer' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'dyson',
                    ],
                ]
            ]
        ]);
        Assert::assertnull($product->getValue('designer', 'fr_FR'));

        $this->get('pim_catalog.updater.property_copier')->copyData(
            $product,
            $product,
            'designer',
            'designer',
            [
                'from_locale' => 'en_US',
                'to_locale' => 'fr_FR',
            ]
        );
        Assert::assertInstanceOf(ReferenceEntityValue::class, $product->getValue('designer', 'fr_FR'));
        Assert::assertSame('dyson', $product->getValue('designer', 'fr_FR')->getData()->__toString());
    }

    /**
     * @test
     */
    public function it_copies_a_reference_entity_collection_value()
    {
        $collectionAttribute = $this->createAttribute(
            [
                'code' => 'designers',
                'group' => 'other',
                'scopable' => false,
                'localizable' => true,
                'type' => ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION,
                'reference_data_name' => 'designers',
            ]
        );

        $product = $this->createProduct(
            [
                'values' => [
                    'designers' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => ['dyson', 'starck'],
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope' => null,
                            'data' => ['newson'],
                        ],
                    ]
                ]
            ]
        );
        Assert::assertSame('newson', $product->getValue('designers', 'fr_FR')->__toString());

        $this->get('pim_catalog.updater.property_copier')->copyData(
            $product,
            $product,
            'designers',
            'designers',
            [
                'from_locale' => 'en_US',
                'to_locale' => 'fr_FR',
            ]
        );

        Assert::assertInstanceOf(ReferenceEntityCollectionValue::class, $product->getValue('designers', 'fr_FR'));
        Assert::assertSame('dyson, starck', $product->getValue('designers', 'fr_FR')->__toString());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadFixtures(): void
    {
        // Enable the fr_FR locale
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $frFr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');
        $channel->addLocale($frFr);
        $this->get('pim_catalog.saver.channel')->save($channel);

        // Create a 'designer reference entity with 3 records
        $createReferenceEntityHandler = $this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler');
        ($createReferenceEntityHandler)(new CreateReferenceEntityCommand('designers', []));
        $createRecordHandler = $this->get('akeneo_referenceentity.application.record.create_record_handler');
        ($createRecordHandler)(new CreateRecordCommand('designers', 'starck', []));
        ($createRecordHandler)(new CreateRecordCommand('designers', 'dyson', []));
        ($createRecordHandler)(new CreateRecordCommand('designers', 'newson', []));
    }

    private function createAttribute(array $data): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, sprintf('validation failed: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function createProduct(array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('some_sku');
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, sprintf('validation failed: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
