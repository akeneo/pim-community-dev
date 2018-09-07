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

namespace Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsTextarea;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ImageAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\TextAttributeDetails;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const RESPONSES_DIR = 'Attribute/ListDetails/';
    private const INDEX_ATTRIBUTE_ROUTE = 'akeneo_enriched_entities_attribute_index_rest';

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
        $this->webClientHelper = $this->get('akeneoenriched_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_lists_all_attributes_for_an_enriched_entity(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::INDEX_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'GET',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ]
        );

        $this->webClientHelper->assertFromFile(
            $this->client->getResponse(),
            self::RESPONSES_DIR . 'ok.json'
        );
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list_if_the_enriched_entity_does_not_have_any_attributes(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::INDEX_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'brand',
            ],
            'GET',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ]
        );

        $expectedContent = [];

        $this->webClientHelper->assertFromFile(
            $this->client->getResponse(),
            self::RESPONSES_DIR . 'ok_empty.json'
        );
    }

    /**
     * @test
     */
    public function it_returns_a_not_found_response_when_the_enriched_entity_identifier_does_not_exists(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::INDEX_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'unknown_enriched_Entity',
            ],
            'GET',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ]
        );

        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    private function loadFixtures(): void
    {
        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_attribute_create', true);

        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntityRepository->create(EnrichedEntity::create(EnrichedEntityIdentifier::fromString('designer'), [], null));
        $enrichedEntityRepository->create(EnrichedEntity::create(EnrichedEntityIdentifier::fromString('brand'), [], null));

        $inMemoryFindAttributesDetailsQuery = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_attributes_details');
        $inMemoryFindAttributesDetailsQuery->save($this->createNameAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createEmailAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createPortraitAttribute());
    }

    private function createNameAttribute(): AbstractAttributeDetails
    {
        $nameAttribute = new TextAttributeDetails();
        $nameAttribute->identifier = AttributeIdentifier::create('designer', 'name', md5('fingerprint'));
        $nameAttribute->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $nameAttribute->code = AttributeCode::fromString('name');
        $nameAttribute->labels = LabelCollection::fromArray(['en_US' => 'Name']);
        $nameAttribute->order = AttributeOrder::fromInteger(0);
        $nameAttribute->isRequired = AttributeIsRequired::fromBoolean(true);
        $nameAttribute->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $nameAttribute->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $nameAttribute->maxLength = AttributeMaxLength::fromInteger(155);
        $nameAttribute->isTextarea = AttributeIsTextarea::fromBoolean(true);
        $nameAttribute->isRichTextEditor = AttributeIsTextarea::fromBoolean(true);
        $nameAttribute->validationRule = AttributeValidationRule::none();
        $nameAttribute->regularExpression = AttributeRegularExpression::createEmpty();

        return $nameAttribute;
    }

    private function createEmailAttribute()
    {
        $emailAttribute = new TextAttributeDetails();
        $emailAttribute->identifier = AttributeIdentifier::create('designer', 'email', md5('fingerprint'));
        $emailAttribute->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $emailAttribute->code = AttributeCode::fromString('email');
        $emailAttribute->labels = LabelCollection::fromArray(['en_US' => 'Name']);
        $emailAttribute->order = AttributeOrder::fromInteger(0);
        $emailAttribute->isRequired = AttributeIsRequired::fromBoolean(true);
        $emailAttribute->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $emailAttribute->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $emailAttribute->maxLength = AttributeMaxLength::fromInteger(155);
        $emailAttribute->isTextarea = AttributeIsTextarea::fromBoolean(false);
        $emailAttribute->isRichTextEditor = AttributeIsTextarea::fromBoolean(false);
        $emailAttribute->validationRule = AttributeValidationRule::fromString(AttributeValidationRule::EMAIL);
        $emailAttribute->regularExpression = AttributeRegularExpression::createEmpty();

        return $emailAttribute;
    }

    private function createPortraitAttribute(): AbstractAttributeDetails
    {
        $imageAttribute = new ImageAttributeDetails();
        $imageAttribute->identifier = AttributeIdentifier::create('designer', 'image', md5('fingerprint'));
        $imageAttribute->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $imageAttribute->code = AttributeCode::fromString('name');
        $imageAttribute->labels = LabelCollection::fromArray(['en_US' => 'Portrait']);
        $imageAttribute->order = AttributeOrder::fromInteger(1);
        $imageAttribute->isRequired = AttributeIsRequired::fromBoolean(true);
        $imageAttribute->valuePerChannel = AttributeValuePerChannel::fromBoolean(true);
        $imageAttribute->valuePerLocale = AttributeValuePerLocale::fromBoolean(true);
        $imageAttribute->maxFileSize = AttributeMaxFileSize::fromString('1000');
        $imageAttribute->allowedExtensions = AttributeAllowedExtensions::fromList(['pdf']);

        return $imageAttribute;
    }
}
