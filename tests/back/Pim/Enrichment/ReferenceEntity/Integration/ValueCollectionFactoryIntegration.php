<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Enrichment\ReferenceEntity\Integration;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert as PhpUnitAssert;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ValueCollectionFactoryIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withReferenceEntity('color_test');
        $this->withRecords('color_test', 'Blue', 'Black');
        $this->withAttributes([
            'singleColor' => [
                'type' => ReferenceEntityType::REFERENCE_ENTITY,
                'reference_entity' => 'color_test'
            ],
            'multipleColor' => [
                'type' => ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION,
                'reference_entity' => 'color_test'
            ]
        ]);
    }

    public function test_it_can_instantiate_a_value_collection_containing_reference_entities_single_link_attribute()
    {
        /** @var WriteValueCollectionFactory $valueCollectionFactory */
        $valueCollectionFactory = $this->get('akeneo.pim.enrichment.factory.write_value_collection');
        $valueCollection = $valueCollectionFactory->createFromStorageFormat([
            'singleColor' => [
                '<all_channels>' => [
                    '<all_locales>' => 'Blue'
                ]
            ],
            'multipleColor' => [
                '<all_channels>' => [
                    '<all_locales>' => ['Blue', 'Black', 'Green']
                ]
            ]
        ]);

        PhpUnitAssert::assertEquals($valueCollection->count(), 2);
        PhpUnitAssert::assertEquals($valueCollection->getByKey('multipleColor-<all_channels>-<all_locales>')->getData(), ['Blue', 'Black']);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function withReferenceEntity(string $identifier): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($identifier);
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $referenceEntityIdentifier,
                [],
                Image::createEmpty())
        );
    }

    private function withRecords(string $referenceEntityIdentifier, string ...$records)
    {
        $identifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        foreach ($records as $record) {
            $recordRepository->create(
                Record::create(
                    RecordIdentifier::fromString($record),
                    $identifier,
                    RecordCode::fromString($record),
                    ValueCollection::fromValues([])
                )
            );
        }
    }

    private function withAttributes(array $attributesData): void
    {
        $attributes = [];
        foreach ($attributesData as $attributeCode => $attributeInfo) {
            $data = [
                'code' => $attributeCode,
                'type' => $attributeInfo['type'],
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
            ];
            /** @var AttributeInterface $attribute */
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $attribute->setProperty('reference_data_name', $attributeInfo['reference_entity']);
            $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

            $constraints = $this->get('validator')->validate($attribute);

            Assert::count($constraints, 0);
            $attributes[] = $attribute;
        }

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }
}
