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
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;

class GetActionTest extends ControllerIntegrationTestCase
{
    private const RESPONSES_DIR = 'Record/RecordDetails/';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function setUp()
    {
        parent::setUp();

        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_a_records_detail()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'ok.json');
    }

    /**
     * @test
     */
    public function it_returns_404_not_found_when_the_record_identifier_does_not_exist()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'not_found.json');
    }

    private function loadFixtures(): void
    {
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
        $this->attributeRepository->create($textAttribute);

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
        $this->attributeRepository->create($textareaAttribute);

        $websiteAttributeIdentifier = AttributeIdentifier::create('designer', 'website', 'fingerprint');
        $websiteAttribute = TextAttribute::createText(
            $websiteAttributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('website'),
            LabelCollection::fromArray(['fr_FR' => 'Website']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::fromString(AttributeValidationRule::URL),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($websiteAttribute);

        // image attribute
        $portraitAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'portrait', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png'])
        );
        $this->attributeRepository->create($portraitAttribute);

        $values = [
            [
                'attribute' => $textAttribute->normalize(),
                'channel' => null,
                'locale' => null,
                'data' => 'Philippe Starck'
            ],
            [
                'attribute' => $textareaAttribute->normalize(),
                'channel' => null,
                'locale' => 'en_US',
                'data' => null,
            ],
            [
                'attribute' => $textareaAttribute->normalize(),
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => null,
            ],
            [
                'attribute' => $websiteAttribute->normalize(),
                'channel' => null,
                'locale' => null,
                'data' => null,
            ],
            [
                'attribute' => $portraitAttribute->normalize(),
                'channel' => null,
                'locale' => null,
                'data' => null,
            ]
        ];

        $starck = new RecordDetails(
            RecordIdentifier::fromString('designer_starck_a1677570-a278-444b-ab46-baa1db199392'),
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('starck'),
            LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']),
            Image::createEmpty(),
            $values,
            true
        );

        $findRecordDetails = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_record_details');
        $findRecordDetails->save($starck);
    }
}
