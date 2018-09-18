<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Integration\UI\Web\Attribute;

use Akeneo\EnrichedEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditActionTest extends ControllerIntegrationTestCase
{
    private const EDIT_ATTRIBUTE_ROUTE = 'akeneo_enriched_entities_attribute_edit_rest';
    private const RESPONSES_DIR = 'Attribute/Edit/';

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
        $this->webClientHelper = $this->get('akeneoenriched_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_edits_a_text_attribute_properties()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'ok.json');
    }

    /**
     * @test
     */
    public function it_does_not_edit_if_the_attribute_does_not_exist()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'attribute_does_not_exist.json');
    }

    /**
     * @test
     * @dataProvider getValidationErrorsRequests
     */
    public function it_normalizes_validation_errors(string $requestFile)
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . $requestFile);
    }

    /**
     * @test
     * @dataProvider getUnsynchronisedIdentifiersRequests
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_one_in_the_body(string $requestFile)
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . $requestFile);
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $attributeIdentifier = sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint'));

        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier'      => $attributeIdentifier,
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_an_error_when_the_user_does_not_have_the_rights()
    {
        $this->revokeEditRights();
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'forbidden.json');
    }

    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_attribute_edit', true);

        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntityRepository->create(EnrichedEntity::create(EnrichedEntityIdentifier::fromString('designer'), [],
            null));
        $enrichedEntityRepository->create(EnrichedEntity::create(EnrichedEntityIdentifier::fromString('brand'), [],
            null));

        $attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', md5('fingerprint')),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(100),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $portrait = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'portrait', md5('fingerprint')),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png'])
        );
        $attributeRepository->create($name);
        $attributeRepository->create($portrait);
    }

    private function revokeEditRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_attribute_edit', false);
    }

    public function getValidationErrorsRequests(): array
    {
        return [
            'Invalid allowed extension' => ['allowed_extensions_is_invalid.json'],
            'Max file size is invalid'  => ['max_file_size_is_invalid.json'],
        ];
    }

    public function getUnsynchronisedIdentifiersRequests()
    {
        return [
            'Unsynchronised attribute identifier'       => ['unsynchronised_attribute_identifier.json'],
            'Unsynchronised enriched entity identifier' => ['unsynchronised_enriched_entity_identifier.json'],
        ];
    }
}
