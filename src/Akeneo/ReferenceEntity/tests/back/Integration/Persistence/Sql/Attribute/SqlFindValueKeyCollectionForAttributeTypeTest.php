<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindValueKeyCollectionForAttributeType;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindValueKeyCollectionForAttributeTypeTest extends SqlIntegrationTestCase
{
    /** @var SqlFindValueKeyCollectionForAttributeType */
    private $findValueKeysForAttributeType;

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    public function setUp()
    {
        parent::setUp();

        $this->findValueKeysForAttributeType = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_value_key_collection_for_attribute_type');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list()
    {
        $actualValueKeys = $this->findValueKeysForAttributeType->fetch(
            ReferenceEntityIdentifier::fromString('designer'),
            'text'
        );
        $this->assertEmpty($actualValueKeys->normalize());
    }

    /**
     * @test
     */
    public function it_finds_all_the_value_keys_for_a_given_attribute_type()
    {
        $this->loadReferenceEntity();
        $this->loadSimpleRecordLinkAttribute('mentor_designer_fingerprint');
        $this->loadSimpleOptionAttribute('designer_favorite_color_fingerprint');

        $actualOptionValueKeys = $this->findValueKeysForAttributeType->fetch($this->referenceEntityIdentifier,
            'option');
        $actualRecordLinkValueKeys = $this->findValueKeysForAttributeType->fetch($this->referenceEntityIdentifier,
            'record');

        $this->assertEquals(['mentor_designer_fingerprint'], $actualRecordLinkValueKeys->normalize());
        $this->assertEquals(['designer_favorite_color_fingerprint'], $actualOptionValueKeys->normalize());
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $this->referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function loadSimpleRecordLinkAttribute(string $attributeIdentifier): void
    {
        $recordAttribute = RecordAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('mentor'),
            LabelCollection::fromArray(['en_US' => 'Mentor']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('designer')
        );
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($recordAttribute);
    }

    private function loadSimpleOptionAttribute(string $attributeIdentifier): void
    {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('favorite_color'),
            LabelCollection::fromArray(['en_US' => 'Favorite color']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }
}
