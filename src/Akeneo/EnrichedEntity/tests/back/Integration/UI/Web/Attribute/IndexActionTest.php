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

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Attribute;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsTextarea;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ImageAttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\TextAttributeDetails;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const RESPONSES_DIR = 'Attribute/ListDetails/';
    private const INDEX_ATTRIBUTE_ROUTE = 'akeneo_reference_entities_attribute_index_rest';

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
    public function it_lists_all_attributes_for_an_reference_entity(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list_if_the_reference_entity_does_not_have_any_attributes(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'empty_list.json');
    }

    /**
     * @test
     */
    public function it_returns_a_not_found_response_when_the_reference_entity_identifier_does_not_exists(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'not_found.json');
    }

    private function loadFixtures(): void
    {
        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_create', true);

        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityRepository->create(ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [],
            Image::createEmpty()
        ));
        $referenceEntityRepository->create(ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [],
            Image::createEmpty()
        ));

        $inMemoryFindAttributesDetailsQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_attributes_details');
        $inMemoryFindAttributesDetailsQuery->save($this->createNameAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createEmailAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createPortraitAttribute());
    }

    private function createNameAttribute(): AbstractAttributeDetails
    {
        $nameAttribute = new TextAttributeDetails();
        $nameAttribute->identifier = AttributeIdentifier::create('designer', 'name', md5('fingerprint'));
        $nameAttribute->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
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
        $emailAttribute->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
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
        $imageAttribute->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
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
