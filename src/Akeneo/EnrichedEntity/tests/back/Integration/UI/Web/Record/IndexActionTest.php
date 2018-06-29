<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const RECORD_LIST_ROUTE = 'akeneo_enriched_entities_record_index_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = $this
            ->get('akeneo_ee_integration_tests.helper.authenticated_client_factory')
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneo_ee_integration_tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_records()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_LIST_ROUTE,
            ['enrichedEntityIdentifier' => 'designer']
        );

        $expectedContent = json_encode([
            'items' => [
                [
                    'identifier'                 => 'starck',
                    'enriched_entity_identifier' => 'designer',
                    'labels'                     => [
                        'en_US' => 'Philippe Starck',
                    ],
                ],
                [
                    'identifier'                 => 'coco',
                    'enriched_entity_identifier' => 'designer',
                    'labels'                     => [
                        'en_US' => 'Coco',
                    ],
                ],
            ],
            'total' => 2,
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), 200, $expectedContent);
    }

    protected function getConfiguration()
    {
        return null;
    }

    private function loadFixtures(): void
    {
        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $recordRepository->save(
            EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString('designer'),
                [
                    'en_US' => 'Designer',
                ]
            )
        );
        $recordRepository->save(
            EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString('manufacturer'),
                [
                    'en_US' => 'Manufacturer',
                    'fr_FR' => 'Fabricant',
                ]
            )
        );

        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $recordRepository->save(
            Record::create(
                RecordIdentifier::fromString('starck'),
                EnrichedEntityIdentifier::fromString('designer'),
                [
                    'en_US' => 'Philippe Starck',
                ]
            )
        );
        $recordRepository->save(
            Record::create(
                RecordIdentifier::fromString('coco'),
                EnrichedEntityIdentifier::fromString('designer'),
                [
                    'en_US' => 'Coco',
                ]
            )
        );

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }
}
