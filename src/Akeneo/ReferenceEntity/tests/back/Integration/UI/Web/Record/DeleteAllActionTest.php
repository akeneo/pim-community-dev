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

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Record;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class DeleteAllActionTest extends ControllerIntegrationTestCase
{
    private const DELETE_ALL_RECORDS_ROUTE = 'akeneo_reference_entities_record_delete_all_rest';
    private const RESPONSES_DIR = 'Record/DeleteAll/';

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
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_deletes_all_records(): void
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
    public function it_does_not_return_an_error_when_the_reference_entity_does_not_exist()
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
            self::DELETE_ALL_RECORDS_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_reference_entity()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ALL_RECORDS_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo.referencentity.infrastructure.persistence.permission.query.can_edit_reference_entity')
            ->forbid();
    }

    private function loadFixtures(): void
    {
        $recordRepository = $this->getRecordRepository();

        $entityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $recordIdentifier = $recordRepository->nextIdentifier($entityIdentifier, $recordCode);
        $recordItem = $this->createRecord($entityIdentifier, $recordCode, $recordIdentifier);
        $recordRepository->create($recordItem);

        $entityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('dyson');
        $recordIdentifier = $recordRepository->nextIdentifier($entityIdentifier, $recordCode);
        $recordItem = $this->createRecord($entityIdentifier, $recordCode, $recordIdentifier);
        $recordRepository->create($recordItem);

        $entityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $recordCode = RecordCode::fromString('cogip');
        $recordIdentifier = $recordRepository->nextIdentifier($entityIdentifier, $recordCode);
        $recordItem = $this->createRecord($entityIdentifier, $recordCode, $recordIdentifier);
        $recordRepository->create($recordItem);

        $entityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $recordCode = RecordCode::fromString('sbep');
        $recordIdentifier = $recordRepository->nextIdentifier($entityIdentifier, $recordCode);
        $recordItem = $this->createRecord($entityIdentifier, $recordCode, $recordIdentifier);
        $recordRepository->create($recordItem);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_records_delete_all', true);
    }

    private function createRecord(
        ReferenceEntityIdentifier $entityIdentifier,
        RecordCode $recordCode,
        RecordIdentifier $recordIdentifier
    ): Record {
        return Record::create(
            $recordIdentifier,
            $entityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
    }

    private function getRecordRepository(): RecordRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_records_delete_all', false);
    }
}
