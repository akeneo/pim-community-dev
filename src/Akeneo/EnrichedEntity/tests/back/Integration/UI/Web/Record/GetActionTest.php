<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\RecordDetails;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\AuthenticatedClientFactory;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Symfony\Bundle\FrameworkBundle\Client;

class GetActionTest extends ControllerIntegrationTestCase
{
    private const RECORD_DETAIL_ROUTE = 'akeneo_enriched_entities_records_get_rest';

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
    public function it_returns_a_records_detail()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_DETAIL_ROUTE,
            ['enrichedEntityIdentifier' => 'designer', 'recordIdentifier' => 'starck']
        );

        $expectedContent = json_encode([
            'identifier'                 => [
                'enriched_entity_identifier' => 'designer',
                'identifier'                 => 'starck',
            ],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'starck',
            'labels'                     => [
                'fr_FR' => 'Philippe Starck',
            ],
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), 200, $expectedContent);
    }

    /**
     * @test
     */
    public function it_returns_404_not_found_when_the_enriched_entity_identifier_does_not_exist()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_DETAIL_ROUTE,
            ['enrichedEntityIdentifier' => 'wrong_enriched_entity', 'recordIdentifier' => 'starck'],
            'GET'
        );
        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_returns_404_not_found_when_the_record_identifier_does_not_exist()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_DETAIL_ROUTE,
            ['enrichedEntityIdentifier' => 'designer', 'recordIdentifier' => 'wrong_record_identifier'],
            'GET'
        );
        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    private function loadFixtures(): void
    {
        $starck = new RecordDetails();
        $starck->identifier = RecordIdentifier::from('designer', 'starck');
        $starck->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $starck->code = RecordCode::fromString('starck');
        $starck->labels = LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']);

        $findRecordDetailsQueryHandler = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_details');
        $findRecordDetailsQueryHandler->save($starck);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }
}
