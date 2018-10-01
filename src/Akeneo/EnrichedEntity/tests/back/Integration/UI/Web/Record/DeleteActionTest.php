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
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class DeleteActionTest extends ControllerIntegrationTestCase
{
    private const DELETE_RECORD_ROUTE = 'akeneo_enriched_entities_record_delete_rest';
    private const RESPONSES_DIR = 'Record/Delete/';

    /* @var Client */
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
    public function it_deletes_a_record_and_its_values(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'ok.json');
    }

    /** @test */
    public function it_returns_an_error_when_the_user_does_not_have_the_rights()
    {
        $this->revokeDeletionRights();
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'forbidden.json');
    }

    /** @test */
    public function it_returns_an_error_when_the_record_does_not_exist()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'delete_not_found.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_RECORD_ROUTE,
            [
                'recordCode' => 'name',
                'enrichedEntityIdentifier' => 'designer',
            ],
            'DELETE'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    private function loadFixtures(): void
    {
        $recordRepository = $this->getRecordRepository();

        $recordItem = Record::create(
            RecordIdentifier::create('designer', 'starck', md5('fingerprint')),
            EnrichedEntityIdentifier::fromString('designer'),
            RecordCode::fromString('starck'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $recordRepository->create($recordItem);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_record_delete', true);
    }

    private function getRecordRepository(): RecordRepositoryInterface
    {
        return $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.record');
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_record_delete', false);
    }
}
