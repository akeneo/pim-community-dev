<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\AuthenticatedClientFactory;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_ENRICHED_ENTITIY_ROUTE = 'akeneo_enriched_entities_enriched_entity_create_rest';

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
    public function it_creates_an_enriched_entity(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ENRICHED_ENTITIY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => 'designer',
                'labels'     => [
                    'fr_FR' => 'Concepteur',
                    'en_US' => 'Designer',
                ],
            ]
        );
        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     */
    public function it_creates_an_enriched_entity_with_no_labels(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ENRICHED_ENTITIY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => 'designer',
            ]
        );
        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     *
     * @param mixed $invalidIdentifier
     */
    public function it_returns_an_error_when_the_identifier_is_not_valid(
        $invalidIdentifier,
        string $expectedResponse
    ): void {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ENRICHED_ENTITIY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => $invalidIdentifier,
                'labels'     => [
                    'fr_FR' => 'Concepteur',
                    'en_US' => 'Designer',
                ],
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
     * @dataProvider invalidLabels
     *
     * @param mixed $invalidLabels
     */
    public function it_returns_an_error_when_the_labels_are_not_valid($invalidLabels, string $expectedResponse): void
    {
        $postContent = [
            'identifier' => 'designer',
        ];
        $postContent = array_merge($postContent, $invalidLabels);

        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ENRICHED_ENTITIY_ROUTE,
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
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
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ENRICHED_ENTITIY_ROUTE,
            [
                'identifier' => 'celine_dion',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    private function loadFixtures(): void
    {
        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }

    public function invalidIdentifiers()
    {
        return [
            'Identifier is null'              => [
                null,
                '{"errors":"{\u0022identifier\u0022:\u0022This value should not be null.\u0022}"}',
            ],
            'Identifier is an integer'        => [
                1234123,
                '{"errors":"{\u0022identifier\u0022:\u0022This value should be of type string.\u0022}"}',
            ],
            'Identifier has a dash character' => [
                'invalid-identifier',
                '{"errors":"{\u0022identifier\u0022:\u0022This field may onlay contain letters, numbers and underscores.\u0022}"}',
            ],
            'Identifier is 256 characters'    => [
                str_repeat('a', 256),
                '{"errors":"{\u0022identifier\u0022:\u0022This value is too long. It should have 255 characters or less.\u0022}"}',
            ],
        ];
    }

    public function invalidLabels()
    {
        return [
            'label as an integer'           => [
                ['labels' => ['fr_FR' => 1]],
                '{"errors":"{\u0022labels\u0022:\u0022invalid label for locale code \\\\\\u0022fr_FR\\\\\\u0022: This value should be of type string.\u0022}"}',
            ],
            'The locale code as an integer' => [
                ['labels' => [1 => 'Designer']],
                '{"errors":"{\u0022labels\u0022:\u0022invalid locale code: This value should be of type string.\u0022}"}',
            ],
        ];
    }
}
