<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\UI\Web\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class SetActionTest extends ControllerIntegrationTestCase
{
    private const SET_REFERENCE_ENTITY_PERMISSION_ROUTE = 'akeneo_reference_entities_reference_entity_permission_set_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_reference_entity_manage_permission', true);
    }

    /**
     * @test
     */
    public function it_sets_user_group_permission_on_a_reference_entity()
    {
        $this->webClientHelper->assertRequest($this->client, 'ReferenceEntityPermission/edit.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_none_of_the_user_groups_have_edit_permission_on_the_reference_entity()
    {
        $this->webClientHelper->assertRequest($this->client, 'ReferenceEntityPermission/no_edit_permission.json');
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
            self::SET_REFERENCE_ENTITY_PERMISSION_ROUTE,
            ['referenceEntityIdentifier' => 'designer'],
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
            self::SET_REFERENCE_ENTITY_PERMISSION_ROUTE,
            ['referenceEntityIdentifier' => 'any_id'],
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
            self::SET_REFERENCE_ENTITY_PERMISSION_ROUTE,
            ['referenceEntityIdentifier' => 'designer'],
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
        $this->get('akeneo_referenceentity.application.reference_entity_permission.can_edit_reference_entity_query_handler')
            ->forbid();
    }

    private function revokeSetPermissionAcls(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_reference_entity_manage_permission', false);
    }
}
