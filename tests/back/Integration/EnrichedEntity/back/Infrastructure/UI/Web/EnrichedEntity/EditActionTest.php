<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Query\EnrichedEntityDetails;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;

class EditActionTest extends TestCase
{
    private const ENRICHED_ENTITIY_EDIT_ROUTE = 'akeneo_enriched_entities_enriched_entity_edit_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = $this->getFromTestContainer('akeneo_ee_integration_tests.helper.authenticated_client_factory')
            ->logIn('julia');
        $this->webClientHelper = $this->getFromTestContainer('akeneo_ee_integration_tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_edits_an_enriched_entity_details()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITIY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => 'designer',
                'labels'     => [
                    'en_US' => 'foo',
                    'fr_FR' => 'bar',
                ],
            ]

        );
        $expectedContent = json_encode([
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur',
            ],
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), '200', $expectedContent);
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request()
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITIY_EDIT_ROUTE,
            ['identifier' => 'unknown_enriched_entity'],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(302, $response->getStatusCode());
    }

    protected function getConfiguration()
    {
        return null;
    }

    private function loadFixtures(): void
    {
        $queryHandler = $this->getFromTestContainer('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');

        $entityItem = EnrichedEntity::create(EnrichedEntityIdentifier::fromString('designer'), [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur',
        ]);
        $queryHandler->save($entityItem);

        $user = new User();
        $user->setUsername('julia');
        $this->getFromTestContainer('pim_user.repository.user')->save($user);
    }
}
