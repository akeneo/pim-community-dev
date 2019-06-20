<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\UI\Web\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\PermissionDetails;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class GetActionTest extends ControllerIntegrationTestCase
{
    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_shows_the_list_of_permissions_for_an_enriched_entity()
    {
        $this->loadFixtures();
        $this->webClientHelper->assertRequest($this->client, 'ReferenceEntityPermission/show.json');
    }

    /**
     * @test
     */
    public function it_shows_an_empty_list_of_permissions()
    {
        $this->webClientHelper->assertRequest($this->client, 'ReferenceEntityPermission/show_empty.json');
    }

    private function loadFixtures(): void
    {
        $query = $this->get('akeneo.referencentity.infrastructure.persistence.query.find_reference_entity_permissions_details');
        $permission = new PermissionDetails();
        $permission->userGroupIdentifier = 1;
        $permission->userGroupName = 'Catalog Manager';
        $permission->rightLevel = 'edit';
        $permissions[] = $permission;

        $permission = new PermissionDetails();
        $permission->userGroupIdentifier = 2;
        $permission->userGroupName = 'IT support';
        $permission->rightLevel = 'edit';
        $permissions[] = $permission;

        $query->save($permissions);
    }
}
