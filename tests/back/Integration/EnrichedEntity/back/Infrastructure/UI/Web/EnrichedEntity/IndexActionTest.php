<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends TestCase
{
    private const ENRICHED_ENTITIY_LIST_ROUTE = 'akeneo_enriched_entities_enriched_entities_index_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.helper.authenticated_client_factory')
            ->logIn('julia');
        $this->webClientHelper = $this->getFromTestContainer('akeneo_ee_integration_tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_enriched_entities()
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
        $this->webClientHelper->assertResponse($this->client->getResponse(), '200', $expectedContent);
    }

    protected function getConfiguration()
    {
        return null;
    }

    private function loadFixtures(): void
    {
        $enrichedEntityRepository = $this->getFromTestContainer('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntityRepository->save(
            EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString('designer'),
                [
                    'en_US' => 'Designer',
                ]
            )
        );

        $enrichedEntityRepository->save(
            EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString('manufacturer'),
                [
                    'en_US' => 'Manufacturer',
                    'fr_FR' => 'Fabricant',
                ]
            )
        );

        $user = new User();
        $user->setUsername('julia');
        $this->getFromTestContainer('pim_user.repository.user')->save($user);
    }
}
