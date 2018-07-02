<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityDetails;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\Client;

class GetActionTest extends TestCase
{
    private const ENRICHED_ENTITIY_DETAIL_ROUTE = 'akeneo_enriched_entities_enriched_entity_get_rest';

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
    public function it_returns_an_enriched_entity_details(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITIY_DETAIL_ROUTE,
            ['identifier' => 'designer']
        );
        $expectedContent = json_encode([
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur',
            ],
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), 200, $expectedContent);
    }

    /**
     * @test
     */
    public function it_returns_404_not_found_when_the_identifier_does_not_exist(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITIY_DETAIL_ROUTE,
            ['identifier' => 'unknown_enriched_entity'],
            'GET'
        );
        $this->webClientHelper->assert404($this->client->getResponse());
    }

    protected function getConfiguration()
    {
        return null;
    }

    private function loadFixtures(): void
    {
        $queryHandler = $this->getFromTestContainer('akeneo_enrichedentity.infrastructure.persistence.query.find_enriched_entity_details');

        $entityItem = new EnrichedEntityDetails();
        $entityItem->identifier = (EnrichedEntityIdentifier::fromString('designer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur',
        ]);
        $queryHandler->save($entityItem);

        $user = new User();
        $user->setUsername('julia');
        $this->getFromTestContainer('pim_user.repository.user')->save($user);
    }
}
