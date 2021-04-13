<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Record;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRequiredValueKeyCollectionForChannelAndLocales;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->get('akeneoreference_entity.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_with_full_text_search()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/ok.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_code_or_label()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/code_label_and_code_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_code_inclusive()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/code_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_option()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/color_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_linked_record()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/city_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_linked_record_and_option()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/city_and_color_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/no_result.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_complete()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/complete_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_uncomplete()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/uncomplete_filtered.json');
    }

    /**
     * @test
     */
    public function it_fails_if_invalid_reference_entity_identifier()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/invalid_reference_entity_identifier.json');
    }

    /**
     * @test
     */
    public function it_fails_if_desynchronized_reference_entity_identifier()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Search/desynchronized_reference_entity_identifier.json');
    }

    private function loadFixtures(): void
    {
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'description', '29aea250-bc94-49b2-8259-bbc116410eb2'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('description'),
                LabelCollection::fromArray(['fr_FR' => 'Nom']),
                AttributeOrder::fromInteger(4),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(512),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'nickname', '29aea250-bc94-49b2-8259-bbc116410eb2'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('nickname'),
                LabelCollection::fromArray(['fr_FR' => 'Surnom']),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(512),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
        $attributeRepository->create(
            OptionAttribute::create(
                AttributeIdentifier::create('designer', 'colors', '52609e00b7ee307e79eb100099b9a8bf'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('colors'),
                LabelCollection::fromArray(['en_US' => 'Color']),
                AttributeOrder::fromInteger(5),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false)
            )
        );
        $attributeRepository->create(
            RecordAttribute::create(
                AttributeIdentifier::create('designer', 'city', '79eb100099b9a8bf52609e00b7ee307e'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('city'),
                LabelCollection::fromArray(['en_US' => 'City']),
                AttributeOrder::fromInteger(6),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                ReferenceEntityIdentifier::fromString('city')
            )
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $referenceEntityIdentifier,
                [],
                Image::createEmpty()
            )
        );

        $cityReferenceEntityIdentifier = ReferenceEntityIdentifier::fromString('city');
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $cityReferenceEntityIdentifier,
                [],
                Image::createEmpty()
            )
        );
        /** @var ReferenceEntity $referenceEntity */
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $attributeAsLabelIdentifier = $referenceEntity->getAttributeAsLabelReference()->getIdentifier();

        // STARCK
        $recordCode = RecordCode::fromString('starck');
        $identifier = RecordIdentifier::fromString('designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2');

        $labelValueEnUS = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Starck')
        );
        $starckDescriptionValue = Value::create(
            AttributeIdentifier::fromString('description_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('an awesome designer!')
        );
        $starkColorValue = Value::create(
            AttributeIdentifier::create('designer', 'colors', '52609e00b7ee307e79eb100099b9a8bf'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            OptionData::createFromNormalize('red')
        );
        $starkCityValue = Value::create(
            AttributeIdentifier::fromString('city_designer_79eb100099b9a8bf52609e00b7ee307e'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            RecordData::createFromNormalize('city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717')
        );

        $recordStarck = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([$labelValueEnUS, $starckDescriptionValue, $starkColorValue, $starkCityValue])
        );
        $recordRepository->create($recordStarck);

        // COCO
        $recordCode = RecordCode::fromString('coco');
        $identifier = RecordIdentifier::fromString('brand_coco_0134dc3e-3def-4afr-85ef-e81b2d6e95fd');

        $cocoDescriptionValue = Value::create(
            AttributeIdentifier::fromString('description_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('You will love coco.')
        );
        $cocoNicknameValue = Value::create(
            AttributeIdentifier::fromString('nickname_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Mr coco')
        );
        $labelValueEnUS = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Coco Chanel')
        );
        $labelValuefrFR = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Coco Chanel')
        );

        $recordCoco = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([$labelValueEnUS, $labelValuefrFR, $cocoDescriptionValue, $cocoNicknameValue])
        );
        $recordRepository->create($recordCoco);

        // DYSON
        $recordCode = RecordCode::fromString('dyson');
        $identifier = RecordIdentifier::fromString('designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd');

        $labelValueEnUS = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Dyson')
        );
        $labelValuefrFR = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Dyson')
        );
        $dysonColorValue = Value::create(
            AttributeIdentifier::create('designer', 'colors', '52609e00b7ee307e79eb100099b9a8bf'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            OptionData::createFromNormalize('red')
        );
        $dysonCityValue = Value::create(
            AttributeIdentifier::fromString('city_designer_79eb100099b9a8bf52609e00b7ee307e'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            RecordData::createFromNormalize('city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717')
        );
        $recordDyson = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([$labelValueEnUS, $labelValuefrFR, $dysonColorValue, $dysonCityValue])
        );
        $recordRepository->create($recordDyson);

        // Paris
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('city');
        /** @var ReferenceEntity $referenceEntity */
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $attributeAsLabelIdentifier = $referenceEntity->getAttributeAsLabelReference()->getIdentifier();
        $recordCode = RecordCode::fromString('paris');
        $identifier = RecordIdentifier::fromString('city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717');
        $labelValueEnUS = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Paris')
        );
        $recordParis = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([$labelValueEnUS])
        );
        $recordRepository->create($recordParis);

        /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredKeyCollectionQuery */
        $findRequiredKeyCollectionQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_required_value_key_collection_for_channel_and_locales');
        $findRequiredKeyCollectionQuery->setActivatedLocales(['en_US', 'fr_FR']);
        $findRequiredKeyCollectionQuery->setActivatedChannels(['ecommerce']);
    }
}
