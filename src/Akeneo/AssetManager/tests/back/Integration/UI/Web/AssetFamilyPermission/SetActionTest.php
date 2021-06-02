<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\UI\Web\AssetFamilyPermission;

use Akeneo\AssetManager\Common\Helper\AuthenticatedClient;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class SetActionTest extends ControllerIntegrationTestCase
{
    private const SET_ASSET_FAMILY_PERMISSION_ROUTE = 'akeneo_asset_manager_asset_family_permission_set_rest';

    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_family_manage_permission', true);
    }

    /**
     * @test
     */
    public function it_sets_user_group_permission_on_an_asset_family()
    {
        $this->webClientHelper->assertRequest($this->client, 'AssetFamilyPermission/edit.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_none_of_the_user_groups_have_edit_permission_on_the_asset_family()
    {
        $this->webClientHelper->assertRequest($this->client, 'AssetFamilyPermission/no_edit_permission.json');
    }

    /**
     * @test
     */
    public function it_returns_an_access_denied_if_the_user_does_not_have_permissions(): void
    {
        $this->client->followRedirects(false);
        $this->forbidsEdit();
        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
            ],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::SET_ASSET_FAMILY_PERMISSION_ROUTE,
            ['assetFamilyIdentifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::SET_ASSET_FAMILY_PERMISSION_ROUTE,
            ['assetFamilyIdentifier' => 'any_id'],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_an_error_when_the_user_do_not_have_the_rights()
    {
        $this->revokeSetPermissionAcls();
        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
            ],
        ];
        $this->webClientHelper->callRoute(
            $this->client,
            self::SET_ASSET_FAMILY_PERMISSION_ROUTE,
            ['assetFamilyIdentifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_assetmanager.application.asset_family_permission.can_edit_asset_family_query_handler')
            ->forbid();
    }

    private function revokeSetPermissionAcls(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_assetmanager_asset_family_manage_permission', false);
    }
}
