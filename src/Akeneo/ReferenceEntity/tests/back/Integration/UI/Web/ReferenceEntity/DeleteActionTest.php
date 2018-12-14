<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\UI\Web\ReferenceEntity;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class DeleteActionTest extends ControllerIntegrationTestCase
{
    private const REFERENCE_ENTITY_DELETE_ROUTE = 'akeneo_reference_entities_reference_entity_delete_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->resetDB();
        $this->loadFixtures();
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_deletes_a_reference_entity_given_an_identifier()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), 204, '');
    }

    /**
     * @test
     */
    public function it_redirects_if_the_request_is_not_an_xml_http_request()
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE'
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_reference_entity_identifier_is_not_valid()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_DELETE_ROUTE,
            ['identifier' => 'des igner'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert500ServerError($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_user_does_not_have_the_acl_to_do_this_action()
    {
        $this->revokeDeletionRights();

        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_there_is_no_reference_entity_with_the_given_identifier()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_DELETE_ROUTE,
            ['identifier' => 'unknown'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_reference_entity_has_some_records()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_DELETE_ROUTE,
            ['identifier' => 'brand'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $expectedResponse = '[{"messageTemplate":"pim_reference_entity.reference_entity.validation.records.should_have_no_record","parameters":{"%reference_entity_identifier%":[]},"plural":null,"message":"You cannot delete this entity because records exist for this entity","root":{"identifier":"brand"},"propertyPath":"","invalidValue":{"identifier":"brand"},"constraint":{"targets":"class","defaultOption":null,"requiredOptions":[],"payload":null},"cause":null,"code":null}]';

        $this->webClientHelper->assertResponse($this->client->getResponse(), 400, $expectedResponse);
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_reference_entity()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_DELETE_ROUTE,
            ['identifier' => 'designer'],
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

    private function getEnrichEntityRepository(): ReferenceEntityRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
    }

    private function getRecordRepository(): RecordRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $referenceEntityRepository = $this->getEnrichEntityRepository();
        $recordRepository = $this->getRecordRepository();

        $entityItem = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($entityItem);

        $entityItem = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'en_US' => 'Brand',
                'fr_FR' => 'Marque',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($entityItem);

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $recordCode = RecordCode::fromString('asus');
        $recordItem = Record::create(
            $recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode),
            $referenceEntityIdentifier,
            $recordCode,
            [
                'en_US' => 'ASUS',
                'fr_FR' => 'ASUS',
            ],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $recordRepository->create($recordItem);

        $fr = new Locale();
        $fr->setId(1);
        $fr->setCode('fr_FR');
        $this->get('pim_catalog.repository.locale')->save($fr);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_reference_entity_delete', true);
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_reference_entity_delete', false);
    }
}
