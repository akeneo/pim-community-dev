<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\RecordItem;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\AuthenticatedClientFactory;
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
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
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
                    'identifier'                 => [
                        'enriched_entity_identifier' => 'designer',
                        'identifier' => 'starck',
                    ],
                    'enriched_entity_identifier' => 'designer',
                    'code' => 'starck',
                    'labels'                     => [
                        'en_US' => 'Philippe Starck',
                    ],
                ],
                [
                    'identifier'                 => [
                        'enriched_entity_identifier' => 'designer',
                        'identifier' => 'coco',
                    ],
                    'enriched_entity_identifier' => 'designer',
                    'code' => 'coco',
                    'labels'                     => [
                        'en_US' => 'Coco',
                    ],
                ],
            ],
            'total' => 2,
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), 200, $expectedContent);
    }

    private function loadFixtures(): void
    {
        $findRecordItems = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_items_for_enriched_entity');
        $findRecordItems->save(
            $this->createRecordItem('starck', 'designer', [ 'en_US' => 'Philippe Starck'])
        );
        $findRecordItems->save(
            $this->createRecordItem('coco', 'designer', ['en_US' => 'Coco'])
        );

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }

    private function createRecordItem(
        string $recordIdentifier,
        string $enrichedEntityIdentifier,
        array $labels
    ): RecordItem {
        $recordItem = new RecordItem();
        $recordItem->identifier = RecordIdentifier::from($enrichedEntityIdentifier, $recordIdentifier);
        $recordItem->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
        $recordItem->code = RecordCode::fromString($recordIdentifier);
        $recordItem->labels = LabelCollection::fromArray($labels);

        return $recordItem;
    }
}
