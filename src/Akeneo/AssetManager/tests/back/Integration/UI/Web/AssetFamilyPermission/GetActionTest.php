<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\UI\Web\AssetFamilyPermission;

use Akeneo\AssetManager\Common\Helper\AuthenticatedClient;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\PermissionDetails;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class GetActionTest extends ControllerIntegrationTestCase
{
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_shows_the_list_of_permissions_for_an_enriched_entity()
    {
        $this->loadFixtures();
        $this->webClientHelper->assertRequest($this->client, 'AssetFamilyPermission/show.json');
    }

    /**
     * @test
     */
    public function it_shows_an_empty_list_of_permissions()
    {
        $this->webClientHelper->assertRequest($this->client, 'AssetFamilyPermission/show_empty.json');
    }

    private function loadFixtures(): void
    {
        $query = $this->get('akeneoassetmanager.infrastructure.persistence.query.find_asset_family_permissions_details');
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
