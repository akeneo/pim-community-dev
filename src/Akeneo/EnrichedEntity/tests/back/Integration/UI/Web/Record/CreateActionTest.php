<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Record;

use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_RECORD_ROUTE = 'akeneo_enriched_entities_record_create_rest';

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
    public function it_creates_a_record(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => [
                    'identifier' => 'intel',
                    'enriched_entity_identifier' => 'brand'
                ],
                'enriched_entity_identifier' => 'brand',
                'code' => 'intel',
                'labels' => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
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
                'enrichedEntityIdentifier' => 'brand',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => [
                    'identifier' => 'intel',
                    'enriched_entity_identifier' => 'brand'
                ],
                'code' => 'intel',
                'enriched_entity_identifier' => 'brand',
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     *
     * @param mixed $recordCode
     * @param mixed $enrichedEntityIdentifier
     * @param mixed $enrichedEntityIdentifierURL
     */
    public function it_returns_an_error_when_the_record_identifier_is_not_valid(
        $recordCode,
        $enrichedEntityIdentifier,
        $enrichedEntityIdentifierURL,
        string $expectedResponse
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => $enrichedEntityIdentifierURL,
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => [
                    'identifier' => $recordCode,
                    'enriched_entity_identifier' => $enrichedEntityIdentifier
                ],
                'enriched_entity_identifier' => $enrichedEntityIdentifier,
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
    public function it_returns_an_error_when_the_record_identifier_is_not_unique() {
        $urlParameters = ['enrichedEntityIdentifier' => 'designer'];
        $headers = ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'];
        $content = [
            'identifier'                 => [
                'identifier'                 => 'starck',
                'enriched_entity_identifier' => 'designer',
            ],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'starck',
            'labels'                     => ['fr_FR' => 'Philippe Starck'],

        ];
        $method = 'POST';
        $this->webClientHelper->callRoute($this->client, self::CREATE_RECORD_ROUTE, $urlParameters, $method, $headers,
            $content);
        $this->webClientHelper->callRoute($this->client, self::CREATE_RECORD_ROUTE, $urlParameters, $method, $headers,
            $content);
        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            '[{"messageTemplate":"pim_enriched_entity.record.validation.identifier.should_be_unique","parameters":{"%enriched_entity_identifier%":"designer","%code%":"starck"},"plural":null,"message":"The record identifier already exists for enriched entity \u0022designer\u0022 and record code \u0022starck\u0022","root":{"identifier":{"identifier":"starck","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"starck","labels":{"fr_FR":"Philippe Starck"}},"propertyPath":"","invalidValue":{"identifier":{"identifier":"starck","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"starck","labels":{"fr_FR":"Philippe Starck"}},"constraint":{"targets":"class","defaultOption":null,"requiredOptions":[],"payload":null},"cause":null,"code":null}]'
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
                'enrichedEntityIdentifier' => 'michel',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => [
                    'identifier' => 'sardoux',
                    'enriched_entity_identifier' => 'michel'
                ],
                'enriched_entity_identifier' => 'michel',
                'labels'     => [
                    'fr_FR' => 'Starck',
                    'en_US' => 'Starck',
                ],
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function loadFixtures(): void
    {
        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_record_create', true);
    }

    private function revokeCreationRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_record_create', false);
    }

    public function invalidIdentifiers()
    {
        $longIdentifier = str_repeat('a', 256);

        return [
            'Record Identifier is null'                                                                  => [
                null,
                'brand',
                'brand',
                '[{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[]},"propertyPath":"identifier","invalidValue":{"identifier":null,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[]},"propertyPath":"identifier","invalidValue":{"identifier":null,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[]},"propertyPath":"code","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[]},"propertyPath":"code","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Record Identifier is an integer'                                                            => [
                1234123,
                'brand',
                'brand',
                '[{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"identifier":{"identifier":1234123,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":1234123,"labels":[]},"propertyPath":"identifier","invalidValue":{"identifier":1234123,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"identifier":{"identifier":1234123,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":1234123,"labels":[]},"propertyPath":"code","invalidValue":1234123,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Record Identifier has a dash character'                                                     => [
                'invalid-identifier',
                'brand',
                'brand',
                '[{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"identifier":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[]},"propertyPath":"identifier","invalidValue":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"identifier":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[]},"propertyPath":"code","invalidValue":"invalid-identifier","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Record Identifier is 256 characters long'                                                   => [
                $longIdentifier,
                'brand',
                'brand',
                sprintf(
                    '[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"identifier":{"identifier":"%s","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"%s","labels":[]},"propertyPath":"identifier","invalidValue":{"identifier":"%s","enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"identifier":{"identifier":"%s","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"%s","labels":[]},"propertyPath":"code","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier
                ),
            ],
            'Enriched Entity Identifier has a dash character'                                            => [
                'intel',
                'invalid-identifier',
                'invalid-identifier',
                '[{"messageTemplate":"pim_enriched_entity.enriched_entity.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"identifier":{"identifier":"intel","enriched_entity_identifier":"invalid-identifier"},"enrichedEntityIdentifier":"invalid-identifier","code":"intel","labels":[]},"propertyPath":"identifier","invalidValue":{"identifier":"intel","enriched_entity_identifier":"invalid-identifier"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"pim_enriched_entity.enriched_entity.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"identifier":{"identifier":"intel","enriched_entity_identifier":"invalid-identifier"},"enrichedEntityIdentifier":"invalid-identifier","code":"intel","labels":[]},"propertyPath":"enrichedEntityIdentifier","invalidValue":"invalid-identifier","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Enriched Entity Identifier is 256 characters long'                                          => [
                'intel',
                $longIdentifier,
                $longIdentifier,
                sprintf(
                    '[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"identifier":{"identifier":"intel","enriched_entity_identifier":"%s"},"enrichedEntityIdentifier":"%s","code":"intel","labels":[]},"propertyPath":"identifier","invalidValue":{"identifier":"intel","enriched_entity_identifier":"%s"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"identifier":{"identifier":"intel","enriched_entity_identifier":"%s"},"enrichedEntityIdentifier":"%s","code":"intel","labels":[]},"propertyPath":"enrichedEntityIdentifier","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier
                ),
            ],
            'Enriched Entity Identifier in the URL is different from the one in the body of the Request' => [
                'intel',
                'brand',
                'brandy',
                '"Enriched Entity Identifier provided in the route and the one given in the body of your request are different"',
            ],
        ];
    }
}
