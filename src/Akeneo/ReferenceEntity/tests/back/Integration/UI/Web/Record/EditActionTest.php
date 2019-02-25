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

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class EditActionTest extends ControllerIntegrationTestCase
{
    private const RECORD_EDIT_ROUTE = 'akeneo_reference_entities_record_edit_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp(): void
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
    public function it_edits_a_record_details(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/details_ok.json');
    }

    /**
     * @test
     */
    public function it_edits_a_record_details_by_removing_the_default_image(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/remove_image_ok.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_EDIT_ROUTE,
            [
                'recordCode'                => 'celine_dion',
                'referenceEntityIdentifier' => 'singer',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $this->webClientHelper->assertRequest($this->client,
            'Record/Edit/unsynchronised_record_identifier.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_reference_entity_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $this->webClientHelper->assertRequest($this->client,
            'Record/Edit/unsynchronised_reference_entity_identifier.json');
    }

    /**
     * @test
     */
    public function it_edits_a_text_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/text_value_ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_we_send_an_invalid_text_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/invalid_text_value.json');
    }

    /**
     * @test
     */
    public function it_edits_a_file_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/image_value_ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_we_send_an_invalid_file_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/invalid_image_value.json');
    }

    /**
     * @test
     */
    public function it_edits_a_record_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/record_value_ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_we_send_an_invalid_record_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/invalid_record_value.json');
    }

    /**
     * @test
     */
    public function it_edits_a_record_collection_value()
    {
        $this->webClientHelper->assertRequest($this->client, 'Record/Edit/record_collection_value_ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_we_send_an_invalid_record_collection_value()
    {
        $this->webClientHelper->assertRequest($this->client,
            'Record/Edit/invalid_record_collection_value.json');
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_reference_entity()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_EDIT_ROUTE,
            [
                'recordCode'                => 'celine_dion',
                'referenceEntityIdentifier' => 'singer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_referenceentity.application.reference_entity_permission.can_edit_reference_entity_query_handler')
            ->forbid();
    }

    private function getRecordRepository(): RecordRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
    }

    private function loadFixtures(): void
    {
        $repository = $this->getRecordRepository();

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg');
        $image = Value::create(
            AttributeIdentifier::fromString('label_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromFileinfo($imageInfo)
        );

        $labelValueEnUS = Value::create(
            AttributeIdentifier::fromString('label_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Philippe Starck')
        );
        $labelValuefrFR = Value::create(
            AttributeIdentifier::fromString('label_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Philippe Starck')
        );
        $entityItem = Record::create(
            RecordIdentifier::fromString('designer_starck_a1677570-a278-444b-ab46-baa1db199392'),
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([$labelValueEnUS, $labelValuefrFR, $image])
        );
        $repository->create($entityItem);

        // text attribute
        $textAttributeIdentifier = AttributeIdentifier::create('designer', 'name', 'fingerprint');
        $textAttribute = TextAttribute::createText(
            $textAttributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->create($textAttribute);

        // textarea attribute
        $textareaAttributeIdentifier = AttributeIdentifier::create('designer', 'description', 'fingerprint');
        $textareaAttribute = TextAttribute::createTextarea(
            $textareaAttributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['fr_FR' => 'Description']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->create($textareaAttribute);

        //website attribute
        $websiteAttributeIdentifier = AttributeIdentifier::create('designer', 'website', 'fingerprint');
        $websiteAttribute = TextAttribute::createText(
            $websiteAttributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('website'),
            LabelCollection::fromArray(['fr_FR' => 'Website']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::fromString(AttributeValidationRule::URL),
            AttributeRegularExpression::createEmpty()
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->create($websiteAttribute);

        // image attribute
        $portraitAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'portrait', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png'])
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->create($portraitAttribute);

        $ikeaRecord = Record::create(
            RecordIdentifier::create('brand', 'ikea', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('ikea'),
            ValueCollection::fromValues([])
        );
        $repository->create($ikeaRecord);

        // record attribute
        $recordAttribute = RecordAttribute::create(
            AttributeIdentifier::create('designer', 'linked_brand', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('linked_brand'),
            LabelCollection::fromArray(['fr_FR' => 'Marque liée', 'en_US' => 'Linked brand']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            ReferenceEntityIdentifier::fromString('brand')
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->create($recordAttribute);

        $parisRecord = Record::create(
            RecordIdentifier::create('city', 'paris', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('city'),
            RecordCode::fromString('paris'),
            ValueCollection::fromValues([])
        );
        $repository->create($parisRecord);
        $lisbonneRecord = Record::create(
            RecordIdentifier::create('city', 'lisbonne', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('city'),
            RecordCode::fromString('lisbonne'),
            ValueCollection::fromValues([])
        );
        $repository->create($lisbonneRecord);
        $moscouRecord = Record::create(
            RecordIdentifier::create('city', 'moscou', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('city'),
            RecordCode::fromString('moscou'),
            ValueCollection::fromValues([])
        );
        $repository->create($moscouRecord);

        // record collection attribute
        $recordCollectionAttribute = RecordCollectionAttribute::create(
            AttributeIdentifier::create('designer', 'linked_cities', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('linked_cities'),
            LabelCollection::fromArray(['fr_FR' => 'Ville', 'en_US' => 'Cities']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            ReferenceEntityIdentifier::fromString('city')
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->create($recordCollectionAttribute);

        $activatedLocales = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }
}
