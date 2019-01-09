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

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Record;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRequiredValueKeyCollectionForChannelAndLocales;
use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
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
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const RESPONSES_DIR = 'Record/Search/';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_with_full_text_search()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'ok.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_code_or_label()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'code_label_and_code_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_code_inclusive()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'code_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'no_result.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_complete()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'complete_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records_filtered_by_uncomplete()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'uncomplete_filtered.json');
    }

    /**
     * @test
     */
    public function it_fails_if_invalid_reference_entity_identifier()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'invalid_reference_entity_identifier.json');
    }

    /**
     * @test
     */
    public function it_fails_if_desynchronized_reference_entity_identifier()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'desynchronized_reference_entity_identifier.json');
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
                AttributeOrder::fromInteger(0),
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
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(512),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        // STARCK
        $recordCode = RecordCode::fromString('starck');
        $identifier = RecordIdentifier::fromString('designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2');

        $starckDescriptionValue = Value::create(
            AttributeIdentifier::fromString('description_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('an awesome designer!')
        );

        $recordStarck = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ['en_US' => 'Starck'],
            Image::createEmpty(),
            ValueCollection::fromValues([$starckDescriptionValue])
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

        $recordCoco = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ['en_US' => 'Coco Chanel', 'fr_FR' => 'Coco Chanel'],
            Image::createEmpty(),
            ValueCollection::fromValues([$cocoDescriptionValue, $cocoNicknameValue])
        );
        $recordRepository->create($recordCoco);

        // DYSON
        $recordCode = RecordCode::fromString('dyson');
        $identifier = RecordIdentifier::fromString('designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd');

        $recordDyson = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ['en_US' => 'Dyson', 'fr_FR' => 'Dyson'],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $recordRepository->create($recordDyson);

        /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredKeyCollectionQuery */
        $findRequiredKeyCollectionQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_required_value_key_collection_for_channel_and_locales');
        $findRequiredKeyCollectionQuery->setActivatedLocales(['en_US', 'fr_FR']);
        $findRequiredKeyCollectionQuery->setActivatedChannels(['ecommerce']);
        $findIdentifiersForQuery = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record.query.find_identifiers_for_query');

        $findIdentifiersForQuery->add($recordDyson);
        $findIdentifiersForQuery->add($recordStarck);
        $findIdentifiersForQuery->add($recordCoco);
    }
}
