<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Record;

use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\AuthenticatedClientFactory;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
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
        $this->webClientHelper = $this->get('akeneo_ee_integration_tests.helper.web_client_helper');
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
                'code' => 'intel',
                'enrichedEntityIdentifier' => 'brand',
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
                'code' => 'intel',
                'enrichedEntityIdentifier' => 'brand',
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     *
     * @param mixed $recordIdentifier
     * @param mixed $enrichedEntityIdentifier
     * @param mixed $enrichedEntityIdentifierURL
     */
    public function it_returns_an_error_when_the_record_identifier_is_not_valid(
        $recordIdentifier,
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
                'enrichedEntityIdentifier' => $enrichedEntityIdentifier,
                'code' => $recordIdentifier,
                'labels' => []
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedResponse
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
                'identifier' => 'starck',
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
                '[{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"enrichedEntityIdentifier":"brand","code":null,"labels":[]},"propertyPath":"code","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"enrichedEntityIdentifier":"brand","code":null,"labels":[]},"propertyPath":"code","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Record Identifier is an integer'                                                            => [
                1234123,
                'brand',
                'brand',
                '[{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"enrichedEntityIdentifier":"brand","code":1234123,"labels":[]},"propertyPath":"code","invalidValue":1234123,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Record Identifier has a dash character'                                                     => [
                'invalid-identifier',
                'brand',
                'brand',
                '[{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[]},"propertyPath":"code","invalidValue":"invalid-identifier","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Record Identifier is 256 characters long'                                                   => [
                $longIdentifier,
                'brand',
                'brand',
                sprintf('[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"enrichedEntityIdentifier":"brand","code":"%s","labels":[]},"propertyPath":"code","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier, $longIdentifier, $longIdentifier),
            ],
            'Enriched Entity Identifier is null'                                                         => [
                'intel',
                null,
                'brand',
                '[{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"enrichedEntityIdentifier":null,"code":"intel","labels":[]},"propertyPath":"enrichedEntityIdentifier","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"enrichedEntityIdentifier":null,"code":"intel","labels":[]},"propertyPath":"enrichedEntityIdentifier","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Enriched Entity Identifier is an integer'                                                   => [
                'intel',
                1234123,
                'brand',
                '[{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"enrichedEntityIdentifier":1234123,"code":"intel","labels":[]},"propertyPath":"enrichedEntityIdentifier","invalidValue":1234123,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Enriched Entity Identifier has a dash character'                                            => [
                'intel',
                'invalid-identifier',
                'brand',
                '[{"messageTemplate":"pim_enriched_entity.enriched_entity.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"enrichedEntityIdentifier":"invalid-identifier","code":"intel","labels":[]},"propertyPath":"enrichedEntityIdentifier","invalidValue":"invalid-identifier","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Enriched Entity Identifier is 256 characters long'                                          => [
                'intel',
                $longIdentifier,
                'brand',
                sprintf('[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"enrichedEntityIdentifier":"%s","code":"intel","labels":[]},"propertyPath":"enrichedEntityIdentifier","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier, $longIdentifier, $longIdentifier),
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
