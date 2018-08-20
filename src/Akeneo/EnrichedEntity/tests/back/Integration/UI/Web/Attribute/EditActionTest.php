<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
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
        $updateLabel = [
            'identifier'                 => [
                'enriched_entity_identifier' => 'designer', // not udpated
                'identifier'                 => 'name', // not updated
            ],
            'enriched_entity_identifier' => 'manufacturer',
            'code'                       => 'A magic name',
            'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'],
            'order'                      => 10,
            'required'                   => false,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'wrong_type',
            'max_length'                 => 200,
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            ['enrichedEntityIdentifier' => 'designer', 'attributeIdentifier' => 'name'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $updateLabel
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);

        $repository = $this->getAttributeRepository();
        $updatedName = $repository->getByIdentifier(AttributeIdentifier::create(
            $updateLabel['identifier']['enriched_entity_identifier'],
            $updateLabel['identifier']['identifier']
        ));

        Assert::assertEquals(
            [
            'identifier'                 => [
                'enriched_entity_identifier' => 'designer', // not updated
                'identifier'                 => 'name', //not updated
            ],
            'enriched_entity_identifier' => 'designer', // not updated
            'code'                       => 'name', // not updated
            'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'], // updated
            'order'                      => 0, // not updated
            'required'                   => false, // updated
            'value_per_channel'          => true, // not updated
            'value_per_locale'           => true, // not updated
            'type'                       => 'text', // not updated
            'max_length'                 => 200, // updated
        ], $updatedName->normalize());
    }

    /**
     * @test
     */
    public function it_edits_an_image_attribute_properties()
    {
        $updateLabel = [
            'identifier'                 => [
                'enriched_entity_identifier' => 'designer', // not updated
                'identifier'                 => 'portrait', // not updated
            ],
            'enriched_entity_identifier' => 'designer2',
            'code'                       => 'new_name',
            'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'],
            'order'                      => 1,
            'required'                   => false,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'wrong_type',
            'max_file_size'              => '500',
            'allowed_extensions'         => ['jpeg'],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            ['enrichedEntityIdentifier' => 'designer', 'attributeIdentifier' => 'portrait'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $updateLabel
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);

        $repository = $this->getAttributeRepository();
        $updatedPortrait = $repository->getByIdentifier(AttributeIdentifier::create(
            $updateLabel['identifier']['enriched_entity_identifier'],
            $updateLabel['identifier']['identifier']
        ));

        Assert::assertEquals(
            [
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'portrait',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'portrait',
                'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'], // updated
                'order'                      => 1,
                'type'                       => 'image',
                'required'                   => false, // updated
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
        $updateLabel = [
            'identifier'                 => [
                'enriched_entity_identifier' => 'designer',
                'identifier'                 => 'unknown_attribute_code',
            ],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'unknown_attribute_code',
            'labels'                     => ['fr_FR' => 'Uknown'],
            'order'                      => 0,
            'required'                   => false,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'wrong_type',
            'max_file_size'              => '500',
            'allowed_extensions'         => ['jpeg'],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            ['enrichedEntityIdentifier' => 'designer', 'attributeIdentifier' => 'portrait'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $updateLabel
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);

        $repository = $this->getAttributeRepository();
        $updatedPortrait = $repository->getByIdentifier(AttributeIdentifier::create(
            $updateLabel['identifier']['enriched_entity_identifier'],
            $updateLabel['identifier']['identifier']
        ));

        Assert::assertEquals(
            [
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'portrait',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'portrait',
                'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'], // updated
                'order'                      => 1,
                'type'                       => 'image',
                'required'                   => false, // updated
                'value_per_channel'          => false,
                'value_per_locale'           => false,
                'max_file_size'              => '500', // updated
                'allowed_extensions'         => ['jpeg'], // updated
            ], $updatedPortrait->normalize());
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier'      => 'name',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_an_error_when_the_user_do_not_have_the_rights()
    {
        $this->revokeCreationRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::EDIT_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'attributeIdentifier'      => 'name',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'name',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'LABEL UPDATED', 'en_US' => 'Name'],
                'order'                      => 0,
                'required'                   => false,
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

        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntityRepository->create(EnrichedEntity::create(EnrichedEntityIdentifier::fromString('designer'), []));
        $enrichedEntityRepository->create(EnrichedEntity::create(EnrichedEntityIdentifier::fromString('brand'), []));

        $attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');
        $name = TextAttribute::create(
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(100)
        );
        $portrait = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'portrait'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(1),
            AttributeRequired::fromBoolean(true),
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
        return $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');
    }

    private function revokeCreationRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_attribute_edit', false);
    }
}
