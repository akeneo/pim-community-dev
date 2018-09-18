<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Integration\UI\Web\Record;

use Akeneo\EnrichedEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Akeneo\EnrichedEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
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
        $this->webClientHelper = $this->get('akeneoenriched_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_a_records_detail()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_DETAIL_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'recordCode' => 'starck'
            ]
        );

        $expectedContent = json_encode([
            'identifier'                 => 'designer_starck_a1677570-a278-444b-ab46-baa1db199392',
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
    public function it_returns_404_not_found_when_the_record_identifier_does_not_exist()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_DETAIL_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
                'recordCode' => 'wrong_record_code'
            ],
            'GET'
        );
        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    private function loadFixtures(): void
    {
        $starck = new RecordDetails();
        $starck->identifier = RecordIdentifier::fromString('designer_starck_a1677570-a278-444b-ab46-baa1db199392');
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
