<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Attribute;

use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Helper\WebClientHelper;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_RECORD_ROUTE = 'akeneo_enriched_entities_attribute_create_rest';

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
    public function it_creates_a_text_attribute(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'order'                      => 0,
                'type'                       => 'text',
                'required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'max_length'                 => 255,
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     */
    public function it_creates_an_image_attribute(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [
                    'fr_FR' => 'Nom',
                    'en_US' => 'Name',
                ],
                'order'                      => 0,
                'type'                       => 'image',
                'required'                   => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'max_file_size'              => 200.15,
                'allowed_extensions'         => ['pdf'],
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     * @dataProvider invalidAttributeTypes
     */
    public function it_returns_an_error_if_the_attribute_type_is_not_provided($invalidAttributeType) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => [
                    'identifier' => 'name',
                    'enriched_entity_identifier' => 'designer'
                ],
                'enriched_entity_identifier' => 'designer',
                'code' => 'name',
                'labels'                     => [],
                'order'                      => 0,
                'type'                       => $invalidAttributeType,
                'required'                   => false,
                'value_per_channel'          => false,
                'value_per_locale'           => false,
                'max_file_size'              => 200.1,
                'allowed_extensions'         => ['pdf'],
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            '"There was no valid attribute type provided in the request"'
        );
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     */
    public function it_returns_an_error_when_the_attribute_identifier_is_not_valid(
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
                'labels'                     => [],
                'order'                      => 0,
                'type'                       => 'image',
                'required'                   => false,
                'value_per_channel'          => false,
                'value_per_locale'           => false,
                'max_file_size'              => 200.1,
                'allowed_extensions'         => ['pdf'],
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
     * @dataProvider invalidOrders
     */
    public function it_returns_an_if_the_order_is_not_valid($order, string $expectedResponse) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'order'                      => $order,
                'type'                       => 'image',
                'required'                   => false,
                'value_per_channel'          => false,
                'value_per_locale'           => false,
                'max_file_size'              => 200,
                'allowed_extensions'         => ['pdf'],
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
     * @dataProvider invalidRequiredValues
     */
    public function it_returns_an_error_if_the_required_flag_is_not_valid($requiredValue, string $expectedMessage) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'order'                      => 1,
                'type'                       => 'image',
                'required'                   => $requiredValue,
                'value_per_channel'          => false,
                'value_per_locale'           => false,
                'max_file_size'              => 200,
                'allowed_extensions'         => ['pdf'],
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedMessage
        );
    }

    /**
     * @test
     * @dataProvider invalidValuePerChannelValues
     */
    public function it_returns_an_error_if_the_value_per_channel_flag_is_invalid(
        $invalidValuePerChannel,
        string $expectedMessage
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'order'                      => 1,
                'type'                       => 'image',
                'required'                   => true,
                'value_per_channel'          => $invalidValuePerChannel,
                'value_per_locale'           => false,
                'max_file_size'              => 200,
                'allowed_extensions'         => ['pdf'],
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedMessage
        );
    }

    /**
     * @test
     * @dataProvider invalidValuePerLocaleValues
     */
    public function it_returns_an_error_if_the_value_per_locale_flag_is_invalid(
        $invalidValuePerLocale,
        string $expectedMessage
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'order'                      => 1,
                'type'                       => 'image',
                'required'                   => true,
                'value_per_channel'          => false,
                'value_per_locale'           => $invalidValuePerLocale,
                'max_file_size'              => 200,
                'allowed_extensions'         => ['pdf'],
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedMessage
        );
    }

    /**
     * @test
     * @dataProvider invalidMaxFileSizes
     */
    public function it_returns_an_error_if_the_max_file_size_value_is_invalid(
        $invalidMaxFileSize,
        string $expectedMessage
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'order'                      => 1,
                'type'                       => 'image',
                'required'                   => true,
                'value_per_channel'          => false,
                'value_per_locale'           => true,
                'max_file_size'              => $invalidMaxFileSize,
                'allowed_extensions'         => ['pdf'],
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
                   $expectedMessage
            );
    }

    /**
     * @test
     * @dataProvider invalidAllowedExtensions
     */
    public function it_returns_an_error_if_the_allowed_extensions_are_invalid(
        $invalidAllowedExtensions,
        string $expectedMessage
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'order'                      => 1,
                'type'                       => 'image',
                'required'                   => true,
                'value_per_channel'          => false,
                'value_per_locale'           => true,
                'max_file_size'              => 150.1,
                'allowed_extensions'         => $invalidAllowedExtensions
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedMessage
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
            'Attribute identifier is null'                                                                  => [
                null,
                'brand',
                'brand',
                '[{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":null,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":null,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Attribute identifier is an integer'                                                            => [
                1234123,
                'brand',
                'brand',
                '[{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":1234123,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":1234123,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":1234123,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":1234123,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":1234123,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":1234123,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Attribute identifier has a dash character'                                                     => [
                'invalid-identifier',
                'brand',
                'brand',
                '[{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":"invalid-identifier","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Attribute identifier is 256 characters long'                                                   => [
                $longIdentifier,
                'brand',
                'brand',
                sprintf(
                    '[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":"%s","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"%s","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":"%s","enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"maxFileSize":200.1,"allowedExtensions":["pdf"],"identifier":{"identifier":"%s","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"%s","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier,
                    $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier
                ),
            ],
        ];
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_RECORD_ROUTE,
            [
                'enrichedEntityIdentifier' => 'celine_dion',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    public function invalidOrders(): array
    {
        return [
            'order is null' => [
                null,
                '[{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":null,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"order","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":null,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"order","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":null,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'order is a string' => [
                '1',
                '[{"messageTemplate":"This value should be of type integer.","parameters":{"{{ value }}":"\u00221\u0022","{{ type }}":"integer"},"plural":null,"message":"This value should be of type integer.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":"1","required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"order","invalidValue":"1","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":"1","required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'order is negative' => [
                -1,
                '[{"messageTemplate":"This value should be greater than or equal to 0.","parameters":{"{{ value }}":"-1","{{ compared_value }}":"0","{{ compared_value_type }}":"integer"},"plural":null,"message":"This value should be greater than or equal to 0.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":-1,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"order","invalidValue":-1,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":-1,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
        ];
    }

    public function invalidRequiredValues()
    {
        return [
            'Required is null' => [
                null,
                '[{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":null,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"required","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":null,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Required is not a boolean' => [
                'wrong_boolean_value',
               '[{"messageTemplate":"This value should be of type boolean.","parameters":{"{{ value }}":"\u0022wrong_boolean_value\u0022","{{ type }}":"boolean"},"plural":null,"message":"This value should be of type boolean.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":"wrong_boolean_value","valuePerChannel":false,"valuePerLocale":false},"propertyPath":"required","invalidValue":"wrong_boolean_value","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":"wrong_boolean_value","valuePerChannel":false,"valuePerLocale":false},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
        ];
    }

    public function invalidValuePerChannelValues()
    {
        return [
            'Value per channel is null' => [
                null,
                '[{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":null,"valuePerLocale":false},"propertyPath":"valuePerChannel","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":null,"valuePerLocale":false},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Value per channel is not a boolean' => [
                'wrong_boolean_value',
                '[{"messageTemplate":"This value should be of type boolean.","parameters":{"{{ value }}":"\u0022wrong_boolean_value\u0022","{{ type }}":"boolean"},"plural":null,"message":"This value should be of type boolean.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":"wrong_boolean_value","valuePerLocale":false},"propertyPath":"valuePerChannel","invalidValue":"wrong_boolean_value","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":"wrong_boolean_value","valuePerLocale":false},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
        ];
    }

    public function invalidValuePerLocaleValues()
    {
        return [
            'Value per locale is null' => [
                null,
                '[{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":null},"propertyPath":"valuePerLocale","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":null},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Value per locale is not a boolean' => [
                'wrong_boolean_value',
                '[{"messageTemplate":"This value should be of type boolean.","parameters":{"{{ value }}":"\u0022wrong_boolean_value\u0022","{{ type }}":"boolean"},"plural":null,"message":"This value should be of type boolean.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":"wrong_boolean_value"},"propertyPath":"valuePerLocale","invalidValue":"wrong_boolean_value","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"200","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":200,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":"wrong_boolean_value"},"propertyPath":"maxFileSize","invalidValue":200,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
        ];
    }

    public function invalidMaxFileSizes()
    {
        return [
            'Max file size is null' => [
                'wrong_file_size',
                '[{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"\u0022wrong_file_size\u0022","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":"wrong_file_size","allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"maxFileSize","invalidValue":"wrong_file_size","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be greater than 0.","parameters":{"{{ value }}":"\u0022wrong_file_size\u0022","{{ compared_value }}":"0","{{ compared_value_type }}":"integer"},"plural":null,"message":"This value should be greater than 0.","root":{"maxFileSize":"wrong_file_size","allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"maxFileSize","invalidValue":"wrong_file_size","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Max file size is a string' => [
                'wrong_file_size',
                '[{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"\u0022wrong_file_size\u0022","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":"wrong_file_size","allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"maxFileSize","invalidValue":"wrong_file_size","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be greater than 0.","parameters":{"{{ value }}":"\u0022wrong_file_size\u0022","{{ compared_value }}":"0","{{ compared_value_type }}":"integer"},"plural":null,"message":"This value should be greater than 0.","root":{"maxFileSize":"wrong_file_size","allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"maxFileSize","invalidValue":"wrong_file_size","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Max file size is an integer' => [
                150,
                '[{"messageTemplate":"This value should be of type float.","parameters":{"{{ value }}":"150","{{ type }}":"float"},"plural":null,"message":"This value should be of type float.","root":{"maxFileSize":150,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"maxFileSize","invalidValue":150,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Max file size is negative' => [
                -1.2512,
                '[{"messageTemplate":"This value should be greater than 0.","parameters":{"{{ value }}":"-1.2512","{{ compared_value }}":"0","{{ compared_value_type }}":"integer"},"plural":null,"message":"This value should be greater than 0.","root":{"maxFileSize":-1.2512,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"maxFileSize","invalidValue":-1.2512,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Max file size is superior than the limit' => [
                10000.15,
                '[{"messageTemplate":"This value should be less than or equal to 9999.99.","parameters":{"{{ value }}":"10000.15","{{ compared_value }}":"9999.99","{{ compared_value_type }}":"double"},"plural":null,"message":"This value should be less than or equal to 9999.99.","root":{"maxFileSize":10000.15,"allowedExtensions":["pdf"],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"maxFileSize","invalidValue":10000.15,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
        ];
    }

    public function invalidAllowedExtensions()
    {
        return [
            'Allowed extensions is null' => [
                null,
                '[{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"array"},"plural":null,"message":"This value should not be blank.","root":{"maxFileSize":150.1,"allowedExtensions":[],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"allowedExtensions","invalidValue":[],"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This field is missing.","parameters":{"{{ field }}":"0"},"plural":null,"message":"This field is missing.","root":{"maxFileSize":150.1,"allowedExtensions":[],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"allowedExtensions","invalidValue":[],"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
            ],
            'Allowed extensions is a list of integers' => [
                ['pdf', 1, 'png', 2],
                '[{"messageTemplate":"This field was not expected.","parameters":{"{{ field }}":"1"},"plural":null,"message":"This field was not expected.","root":{"maxFileSize":150.1,"allowedExtensions":["pdf",1,"png",2],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"allowedExtensions","invalidValue":["pdf",1,"png",2],"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This field was not expected.","parameters":{"{{ field }}":"2"},"plural":null,"message":"This field was not expected.","root":{"maxFileSize":150.1,"allowedExtensions":["pdf",1,"png",2],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"allowedExtensions","invalidValue":["pdf",1,"png",2],"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This field was not expected.","parameters":{"{{ field }}":"3"},"plural":null,"message":"This field was not expected.","root":{"maxFileSize":150.1,"allowedExtensions":["pdf",1,"png",2],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":true,"valuePerChannel":false,"valuePerLocale":true},"propertyPath":"allowedExtensions","invalidValue":["pdf",1,"png",2],"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
        ];
    }

    public function invalidAttributeTypes()
    {
        return [
            'Attribute type is null' => [null],
            'Attribute type is a integer' => [150]
        ];
    }
}
