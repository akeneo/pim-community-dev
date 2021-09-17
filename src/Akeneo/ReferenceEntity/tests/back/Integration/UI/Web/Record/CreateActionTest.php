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

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClient;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_RECORD_ROUTE = 'akeneo_reference_entities_record_create_rest';

    /** @var WebClientHelper */
    private $webClientHelper;

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
    public function it_creates_a_record(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'referenceEntityIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'reference_entity_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
        $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer')->assertIndexRefreshed();
    }

    /**
     * @test
     */
    public function it_creates_a_record_with_no_label(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'referenceEntityIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'code' => 'intel',
                'reference_entity_identifier' => 'brand',
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
        $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer')->assertIndexRefreshed();
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     *
     * @param mixed $recordCode
     * @param mixed $referenceEntityIdentifier
     * @param mixed $referenceEntityIdentifierURL
     */
    public function it_returns_an_error_when_the_record_identifier_is_not_valid(
        $recordCode,
        $referenceEntityIdentifier,
        $referenceEntityIdentifierURL,
        string $expectedResponse
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'referenceEntityIdentifier' => $referenceEntityIdentifierURL,
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'reference_entity_identifier' => $referenceEntityIdentifier,
                'code' => $recordCode,
                'labels' => []
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedResponse
        );
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_record_identifier_is_not_unique()
    {
        $urlParameters = ['referenceEntityIdentifier' => 'designer'];
        $headers = ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'];
        $content = [
            'identifier'                 => 'designer_starck_1',
            'reference_entity_identifier' => 'designer',
            'code'                       => 'starck',
            'labels'                     => ['fr_FR' => 'Philippe Starck'],

        ];
        $method = 'POST';
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            $urlParameters,
            $method,
            $headers,
            $content
        );
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            $urlParameters,
            $method,
            $headers,
            $content
        );
        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            '[{"messageTemplate":"pim_reference_entity.record.validation.code.should_be_unique","parameters":{"%reference_entity_identifier%":[],"%code%":[]},"plural":null,"message":"The record code already exists for reference entity \u0022designer\u0022 and record code \u0022starck\u0022","root":{"referenceEntityIdentifier":"designer","code":"starck","labels":{"fr_FR":"Philippe Starck"}},"propertyPath":"code","invalidValue":{"referenceEntityIdentifier":"designer","code":"starck","labels":{"fr_FR":"Philippe Starck"}},"constraint":{"targets":"class","defaultOption":null,"requiredOptions":[],"payload":null},"cause":null,"code":null}]'
        );
    }

    /** @test */
    public function it_returns_an_error_when_the_user_do_not_have_the_rights()
    {
        $this->revokeCreationRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'referenceEntityIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'reference_entity_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_reference_entity()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'referenceEntityIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => 'brand_intel_a1677570-a278-444b-ab46-baa1db199392',
                'reference_entity_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_referenceentity.application.reference_entity_permission.can_edit_reference_entity_query_handler')
            ->forbid();
    }

    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_record_create', true);

        $activatedLocales = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }

    private function revokeCreationRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_record_create', false);
    }

    public function invalidIdentifiers()
    {
        $longIdentifier = str_repeat('a', 256);

        return [
            'Record Identifier has a dash character'                                                     => [//rework as it should not be possible
                'invalid-code',
                'brand',
                'brand',
                '[{"messageTemplate":"pim_reference_entity.record.validation.code.pattern","parameters":{"{{ value }}":"\u0022invalid-code\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"referenceEntityIdentifier":"brand","code":"invalid-code","labels":[]},"propertyPath":"code","invalidValue":"invalid-code","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'            ],
            'Record Identifier is 256 characters long'                                                   => [
                $longIdentifier,
                'brand',
                'brand',
                sprintf(
                    '[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"referenceEntityIdentifier":"brand","code":"%s","labels":[]},"propertyPath":"code","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier,
                    $longIdentifier,
                    $longIdentifier
                ),
            ],
        ];
    }
}
