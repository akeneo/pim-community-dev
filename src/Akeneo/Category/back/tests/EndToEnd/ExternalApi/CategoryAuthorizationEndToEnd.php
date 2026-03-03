<?php

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\HttpFoundation\Response;

class CategoryAuthorizationEndToEnd extends ApiTestCase
{
    private static string $PIM_API_OVERALL_ACCESS_ROLE = 'pim_api_overall_access';
    private static string $PIM_API_CATEGORY_LIST_ROLE = 'pim_api_category_list';
    private static string $PIM_API_CATEGORY_EDIT_ROLE = 'pim_api_category_edit';

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoleWithAcls(self::$PIM_API_OVERALL_ACCESS_ROLE, ['pim_api_overall_access']);
        $this->createRoleWithAcls(self::$PIM_API_CATEGORY_LIST_ROLE, ['pim_api_category_list']);
        $this->createRoleWithAcls(self::$PIM_API_CATEGORY_EDIT_ROLE, ['pim_api_category_edit']);
    }

    public function testOverallAccessDenied(): void
    {
        $client = $this->createAuthenticatedClient(username: 'kevin', password: 'kevin');

        $client->request('GET', '/api/rest/v1/categories');

        $expectedResponse = <<<JSON
        {
            "code": 403,
            "message": "You are not allowed to access the web API."
        }
        JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForListingCategories(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');

        $this->addRoleToUser('julia', self::$PIM_API_CATEGORY_LIST_ROLE);

        $client->request('GET', '/api/rest/v1/categories');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingCategories(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');

        $client->request('GET', '/api/rest/v1/categories');

        $expectedResponse = <<<JSON
        {
            "code": 403,
            "message": "You are not allowed to access the web API."
        }
        JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForGettingACategory(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');

        $this->addRoleToUser('julia', self::$PIM_API_CATEGORY_LIST_ROLE);

        $client->request('GET', '/api/rest/v1/categories/master');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForGettingACategory(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');

        $client->request('GET', '/api/rest/v1/categories/master');

        $expectedResponse = <<<JSON
        {
            "code": 403,
            "message": "You are not allowed to access the web API."
        }
        JSON;

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForCreatingACategory(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');

        $this->addRoleToUser('julia', self::$PIM_API_CATEGORY_EDIT_ROLE);

        $data = <<<JSON
        {
            "code": "new_category"
        }
        JSON;

        $client->request('POST', '/api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAccessDeniedForCreatingACategory(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');

        $data = <<<JSON
        {
            "code": "super_new_category"
        }
        JSON;

        $client->request('POST', '/api/rest/v1/categories', [], [], [], $data);

        $expectedResponse = <<<JSON
        {
            "code": 403,
            "message": "You are not allowed to access the web API."
        }
        JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingACategory(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');

        $this->addRoleToUser('julia', self::$PIM_API_CATEGORY_EDIT_ROLE);

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/categories/master', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testAccessDeniedForPartialUpdatingACategory(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/categories/master', [], [], [], $data);

        $expectedResponse = <<<JSON
        {
            "code": 403,
            "message": "You are not allowed to access the web API."
        }
        JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingAListOfCategories(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data = <<<JSON
        {"code": "a_category"}
        JSON;

        ob_start(function () {
            return '';
        });
        $client->request('PATCH', '/api/rest/v1/categories', [], [], [], $data);
        ob_end_flush();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForPartialUpdatingAListOfCategories(): void
    {
        $client = $this->createAuthenticatedClient(username: 'julia', password: 'julia');
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data = <<<JSON
        {"code": "a_category"}
        JSON;
        ob_start(function () {
            return '';
        });
        $client->request('PATCH', '/api/rest/v1/categories', [], [], [], $data);
        ob_end_flush();

        $expectedResponse = <<<JSON
        {
            "code": 403,
            "message": "You are not allowed to access the web API."
        }
        JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param array<int, string> $acls
     */
    private function createRoleWithAcls(string $roleCode, array $acls): void
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->get('oro_security.acl.manager');
        /** @var UnitOfWorkAndRepositoriesClearer $cacheClearer */
        $cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
        /** @var SimpleFactoryInterface $roleFactory */
        $roleFactory = $this->get('pim_user.factory.role');
        /** @var SaverInterface $roleSaver */
        $roleSaver = $this->get('pim_user.saver.role');
        /** @var RoleWithPermissionsRepository $roleWithPermissionsRepository */
        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        /** @var RoleWithPermissionsSaver $roleWithPermissionsSaver */
        $roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');

        /** @var Role $role */
        $role = $roleFactory->create();
        $role->setRole($roleCode);
        $role->setLabel($roleCode);
        $roleSaver->save($role);

        $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        assert(null !== $roleWithPermissions);

        $permissions = $roleWithPermissions->permissions();
        foreach ($acls as $acl) {
            $permissions[sprintf('action:%s', $acl)] = true;
        }
        $roleWithPermissions->setPermissions($permissions);

        $roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $aclManager->flush();
        $aclManager->clearCache();
        $cacheClearer->clear();
    }

    private function addRoleToUser(string $username, string $roleName): void
    {
        /** @var UserRepositoryInterface $userRpository */
        $userRpository = $this->get('pim_user.repository.user');
        /** @var RoleRepositoryInterface $roleRepository */
        $roleRepository = $this->get('pim_user.repository.role');
        /** @var UserInterface $user */
        $user = $userRpository->findOneByIdentifier($username);

        /** @var Role $role */
        $role = $roleRepository->findOneByIdentifier($roleName);
        $user->addRole($role);
        /** @var SaverInterface $userSaver */
        $userSaver = $this->get('pim_user.saver.user');
        $userSaver->save($user);
    }
}
