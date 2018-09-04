<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Attribute;

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
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
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

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    private const RESPONSES_DIR = 'Attribute/Edit/';

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
        $attributeIdentifier = sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint'));

        $updateAllProperties = [
            'identifier'                 => $attributeIdentifier,
            'enriched_entity_identifier' => 'manufacturer',
            'code'                       => 'A magic name',
            'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'],
            'order'                      => 10,
            'is_required'                => false,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'wrong_type',
            'max_length'                 => 200,
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier' => $attributeIdentifier
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $updateAllProperties
        );

        $this->webClientHelper->assertFromFile($this->client->getResponse(), self::RESPONSES_DIR . 'ok.json');
    }

    /**
     * @test
     */
    public function it_edits_an_image_attribute_properties()
    {
        $attributeIdentifier = sprintf('%s_%s_%s', 'portrait', 'designer', md5('fingerprint'));

        $updateAllProperties = [
            'identifier'                 => $attributeIdentifier,
            'enriched_entity_identifier' => 'designer2',
            'code'                       => 'new_name',
            'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'],
            'order'                      => 1,
            'is_required'                   => false,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'wrong_type',
            'max_file_size'              => '500',
            'allowed_extensions'         => ['jpeg'],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier' => $attributeIdentifier
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $updateAllProperties
        );

        $this->webClientHelper->assertFromFile($this->client->getResponse(), self::RESPONSES_DIR . 'ok.json');

        $repository = $this->getAttributeRepository();
        $updatedPortrait = $repository->getByIdentifier(AttributeIdentifier::fromString(
            sprintf('%s_%s_%s', 'portrait', 'designer', md5('fingerprint'))
        ));

        Assert::assertEquals(
            [
                'identifier'                 => sprintf('%s_%s_%s', 'portrait', 'designer', md5('fingerprint')),
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'portrait',
                'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'], // updated
                'order'                      => 1,
                'type'                       => 'image',
                'is_required'                   => false, // updated
                'value_per_channel'          => false,
                'value_per_locale'           => false,
                'max_file_size'              => '500', // updated
                'allowed_extensions'         => ['jpeg'], // updated
            ], $updatedPortrait->normalize());
    }

    /**
     * @test
     */
    public function it_does_not_edit_if_the_attribute_does_not_exist()
    {
        $attributeIdentifier = 'unknown';

        $updateUnkownAttribute = [
            'identifier'                 => $attributeIdentifier,
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'unknown_attribute_code',
            'labels'                     => ['fr_FR' => 'Uknown'],
            'order'                      => 0,
            'is_required'                => false,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'wrong_type',
            'max_file_size'              => '500.2',
            'allowed_extensions'         => ['jpeg'],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier' => $attributeIdentifier
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $updateUnkownAttribute
        );

        $this->webClientHelper->assertFromFile(
            $this->client->getResponse(),
            self::RESPONSES_DIR . 'attribute_does_not_exist.json'
        );
    }

    /**
     * @test
     */
    public function it_does_not_edit_if_the_max_file_size_property_is_invalid()
    {
        $attributeIdentifier = sprintf('%s_%s_%s', 'portrait', 'designer', md5('fingerprint'));

        $invalidMaxFileSize = [
            'identifier'                 => $attributeIdentifier,
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'portrait',
            'labels'                     => ['fr_FR' => 'Uknown'],
            'order'                      => 0,
            'is_required'                   => false,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'wrong_type',
            'max_file_size'              => '-1',
            'allowed_extensions'         => ['jpeg'],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier' => $attributeIdentifier
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $invalidMaxFileSize
        );

        $this->webClientHelper->assertFromFile(
            $this->client->getResponse(),
            self::RESPONSES_DIR . 'max_file_size_is_invalid.json'
        );
    }

    /**
     * @test
     */
    public function it_does_not_edit_if_the_allowed_extensions_is_invalid()
    {
        $attributeIdentifier = sprintf('%s_%s_%s', 'portrait', 'designer', md5('fingerprint'));

        $invalidAllowedExtensions = [
            'identifier'                 => $attributeIdentifier,
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'portrait',
            'labels'                     => ['fr_FR' => 'Image autobiographique', 'en_US' => 'Name'], // updated
            'order'                      => 1,
            'type'                       => 'image',
            'is_required'                   => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'max_file_size'              => '200.10',
            'allowed_extensions'         => [150, 122], // updated
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier' => $attributeIdentifier
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $invalidAllowedExtensions
        );

        $this->webClientHelper->assertFromFile(
            $this->client->getResponse(),
            self::RESPONSES_DIR . 'allowed_extensions_is_invalid.json'
        );
    }

    /**
     * @test
     * @dataProvider invalidIdentifierForUrlAndBody
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_body(
        string $enrichedEntityIdentifierUrl,
        string $attributeIdentifierUrl,
        string $attributeIdentifierBody
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => $enrichedEntityIdentifierUrl,
                'attributeIdentifier' => $attributeIdentifierUrl
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => $attributeIdentifierBody
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            '"The identifier provided in the route and the one given in the body of the request are different"'
        );
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
        $attributeIdentifier = sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint'));

        $this->revokeCreationRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier'      => $attributeIdentifier,
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => $attributeIdentifier,
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                   => false,
                'value_per_channel'          => false,
                'value_per_locale'           => false,
                'type'                       => 'text',
                'max_length'                 => 200,
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_attribute_edit', true);

        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntityRepository->create(EnrichedEntity::create(EnrichedEntityIdentifier::fromString('designer'), [], null));
        $enrichedEntityRepository->create(EnrichedEntity::create(EnrichedEntityIdentifier::fromString('brand'), [], null));

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

    private function getAttributeRepository(): AttributeRepositoryInterface
    {
        return $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
    }

    private function revokeCreationRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_attribute_edit', false);
    }

    public function invalidIdentifierForUrlAndBody()
    {
        return [
            'attribute identifier is not the same' => [
                'designer',
                'not_the_same_attribute_code_url',
                'not_the_same_attribute_code_body',
            ]
        ];
    }
}
