<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityItem;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Helper\WebClientHelper;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const ENRICHED_ENTITIY_LIST_ROUTE = 'akeneo_enriched_entities_enriched_entity_index_rest';

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
    public function it_returns_a_list_of_enriched_entities(): void
    {
        $this->webClientHelper->callRoute($this->client, self::ENRICHED_ENTITIY_LIST_ROUTE);

        $expectedContent = json_encode([
            'items' => [
                [
                    'identifier' => 'designer',
                    'labels'     => [
                        'en_US' => 'Designer',
                    ],
                ],
                [
                    'identifier' => 'manufacturer',
                    'labels'     => [
                        'en_US' => 'Manufacturer',
                        'fr_FR' => 'Fabricant',
                    ],
                ],
            ],
            'total' => 2,
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), 200, $expectedContent);
    }

    private function loadFixtures(): void
    {
        $queryHandler = $this->get(
            'akeneo_enrichedentity.infrastructure.persistence.query.find_enriched_entity_items'
        );

        $entityItem = new EnrichedEntityItem();
        $entityItem->identifier = (EnrichedEntityIdentifier::fromString('designer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Designer',
        ]);
        $queryHandler->save($entityItem);

        $entityItem = new EnrichedEntityItem();
        $entityItem->identifier = (EnrichedEntityIdentifier::fromString('manufacturer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Manufacturer',
            'fr_FR' => 'Fabricant',
        ]);
        $queryHandler->save($entityItem);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }
}
