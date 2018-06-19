<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\Test\Integration\TestCase;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class GetActionTest extends TestCase
{
    private const ENRICHED_ENTITIY_DETAIL_ROUTE = 'akeneo_enriched_entities_enriched_entities_get_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = $this->getFromTestContainer('akeneo_ee_integration_tests.helper.authenticated_client_factory')
            ->logIn('admin');
        $this->webClientHelper = $this->getFromTestContainer('akeneo_ee_integration_tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_an_enriched_entity_details()
    {
        $this->callRoute($this->client, self::ENRICHED_ENTITIY_DETAIL_ROUTE, ['identifier' => 'designer']);
        $expectedContent = json_encode([
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'Designer',
            ],
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), '200', $expectedContent);
    }

    /**
     * @test
     */
    public function it_returns_404_not_found_when_the_identifier_does_not_exist()
    {
        $this->callRoute(
            $this->client,
            self::ENRICHED_ENTITIY_DETAIL_ROUTE,
            ['identifier' => 'unknown_enriched_entity'],
            'GET'
        );
        $this->assertResponse($this->client->getResponse(), '404', '{}');
    }

    protected function getConfiguration()
    {
        return null;
    }

    private function loadFixtures(): void
    {
        $this->getFromTestContainer('akeneo_enrichedentity.infrastructure.persistence.enriched_entity')->save(
            EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString('designer'),
                [
                    'en_US' => 'Designer',
                ]
            )
        );
    }

    private function callRoute(Client $client, string $route, array $arguments = [], string $method = 'GET'): void
    {
        $url = $this->get('router')->generate($route, $arguments);
        $client->request($method, $url, [], [], [], json_encode([]));
    }

    private function assertResponse(Response $response, string $statusCode, string $expectedContent = ''): void
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($expectedContent, $response->getContent());
    }
}
