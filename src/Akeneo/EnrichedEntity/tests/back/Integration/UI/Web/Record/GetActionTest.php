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

namespace Akeneo\EnrichedEntity\Integration\UI\Web\Record;

use Akeneo\EnrichedEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;

class GetActionTest extends ControllerIntegrationTestCase
{
    private const RECORD_DETAIL_ROUTE = 'akeneo_enriched_entities_records_get_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    private const RESPONSES_DIR = 'Record/RecordDetails/';

    public function setUp()
    {
        parent::setUp();

        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoenriched_entity.tests.helper.web_client_helper');
        $this->attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_a_records_detail()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_DETAIL_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'recordCode' => 'starck'
            ]
        );

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
        $textareaAttributeIdentifier = AttributeIdentifier::create('designer', 'name', 'fingerprint');
        $textAttribute = TextAttribute::createText(
            $textareaAttributeIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
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
            EnrichedEntityIdentifier::fromString('designer'),
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

        $values = [
            'name_designer_fingerprint' => [
                'attribute' => $textAttribute->normalize(),
                'channel' => null,
                'locale' => null,
                'data' => 'Philippe Starck'
            ],
            'description_designer_fingerprint_en_US' => [
                'attribute' => $textareaAttribute->normalize(),
                'channel' => null,
                'locale' => 'en_US',
                'data' => null,
            ],
            'description_designer_fingerprint_fr_FR' => [
                'attribute' => $textareaAttribute->normalize(),
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => null,
            ],
        ];

        $starck = new RecordDetails(
            RecordIdentifier::fromString('designer_starck_a1677570-a278-444b-ab46-baa1db199392'),
            EnrichedEntityIdentifier::fromString('designer'),
            RecordCode::fromString('starck'),
            LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']),
            $values
        );

        $findRecordDetails = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_details');
        $findRecordDetails->save($starck);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }
}
