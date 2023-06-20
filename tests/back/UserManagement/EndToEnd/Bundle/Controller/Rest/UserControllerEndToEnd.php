<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\EndToEnd\Bundle\Controller\Rest;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Application\Command\UpdateUserCommand\UpdateUserCommand;
use Akeneo\UserManagement\Application\Command\UpdateUserCommand\UpdateUserCommandHandler;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Bundle\Security\UserProvider;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserCommand;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserHandlerInterface;
use AkeneoTest\UserManagement\Helper\ControllerEndToEndTestCase;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class UserControllerEndToEnd extends ControllerEndToEndTestCase
{
    private readonly UserPasswordHasherInterface $userPasswordHasher;
    private readonly UserLoader $userLoader;
    private readonly RoleWithPermissionsRepository $roleWithPermissionsRepository;
    private readonly AclManager $aclManager;
    private readonly UnitOfWorkAndRepositoriesClearer $cacheClearer;
    private readonly SimpleFactoryInterface $roleFactory;
    private readonly SaverInterface $roleSaver;
    private readonly RoleWithPermissionsSaver $roleWithPermissionsSaver;
    private readonly AccessDecisionManagerInterface $decisionManager;
    private readonly DeleteUserHandlerInterface $deleteUserHandler;
    private readonly UpdateUserCommandHandler $updateUserCommandHandler;
    private readonly UserProvider $userProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userPasswordHasher = $this->get(UserPasswordHasherInterface::class);
        $this->userLoader = $this->get(UserLoader::class);
        $this->roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $this->aclManager = $this->get('oro_security.acl.manager');
        $this->cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
        $this->roleFactory = $this->get('pim_user.factory.role');
        $this->roleSaver = $this->get('pim_user.saver.role');
        $this->roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');
        $this->decisionManager = $this->get('security.access.decision_manager');
        $this->deleteUserHandler = $this->get(DeleteUserHandlerInterface::class);
        $this->updateUserCommandHandler = $this->get(UpdateUserCommandHandler::class);
        $this->userProvider = $this->get('pim_user.provider.user');

        $this->logAs('julia');
    }

    public function testItUpdatePassword(): void
    {
        $user = $this->userLoader->createUser('Julien', [], ['ROLE_USER']);

        $newPassword = 'newJulien';

        $this->assertTrue($this->userPasswordHasher->isPasswordValid($user, 'Julien'));
        $this->assertFalse($this->userPasswordHasher->isPasswordValid($user, $newPassword));

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_user_user_rest_post',
            routeArguments: [
                'identifier' => (string) $user->getId(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'current_password' => 'Julien',
                'new_password' => $newPassword,
                'new_password_repeat' => $newPassword
            ]),
        );

        $expectedContent = [
            'code'=> $user->getUserIdentifier(),
            'username'=> $user->getUserIdentifier(),
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ];
        $responseJson = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $responseJson->getStatusCode());
        $response = json_decode($responseJson->getContent(), true);
        $this->assertEquals($expectedContent['code'], $response['code']);
        $this->assertEquals($expectedContent['email'], $response['email']);
        $this->assertEquals($expectedContent['username'], $response['username']);
        $this->assertEquals($expectedContent['first_name'], $response['first_name']);
        $this->assertEquals($expectedContent['last_name'], $response['last_name']);

        $this->assertFalse($this->userPasswordHasher->isPasswordValid($user, 'Julien'));
        $this->assertTrue($this->userPasswordHasher->isPasswordValid($user, $newPassword));
    }

    public function testItUpdateUser(): void
    {
        $user = $this->userLoader->createUser('Julien', [], ['ROLE_USER']);

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_user_user_rest_post',
            routeArguments: [
                'identifier' => (string) $user->getId(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => $user->getUserIdentifier(),
                'enabled'=> true,
                'username' => $user->getUserIdentifier(),
                'email'=> 'test@test.fr',
                'name_prefix' => null,
                'first_name'=> $user->getFirstName(),
                'middle_name' => null,
                'last_name'=> $user->getLastName(),
                'name_suffix' => null,
                'phone'=> null,
                'image' => null,
                'last_login'=> null,
                'catalog_default_locale'=> 'en_US',
                'user_default_locale' => 'fr_FR',
                'catalog_default_scope'=> 'ecommerce',
                'default_category_tree' => $user->getDefaultTree()->getCode(),
                'email_notifications'=> false,
                'timezone' => 'Africa/Djibouti',
                'groups'=> ['Redactor'],
                'visible_group_ids' => [],
                'roles'=> ['ROLE_USER', 'ROLE_CATALOG_MANAGER'],
                'product_grid_filters' => [],
                'profile'=> null,
                'avatar' => [
                    'filePath'=> null,
                    'originalFilename' => null
                ],
                'properties' => []
            ]),
        );

        $expectedContent = [
            'code'=> $user->getUserIdentifier(),
            'username'=> $user->getUserIdentifier(),
            'email' => 'test@test.fr',
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ];
        $responseJson = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $responseJson->getStatusCode());
        $response = json_decode($responseJson->getContent(), true);
        $this->assertEquals($expectedContent['code'], $response['code']);
        $this->assertEquals($expectedContent['email'], $response['email']);
        $this->assertEquals($expectedContent['username'], $response['username']);
        $this->assertEquals($expectedContent['first_name'], $response['first_name']);
        $this->assertEquals($expectedContent['last_name'], $response['last_name']);

        $this->deleteUser('Julien');
    }

    public function testItRemoveLastEditRoleToUser(): void
    {
        $this->createRoleWithAcls('ROLE_WITHOUT_EDIT_ROLE', ['action:oro_config_system']);
        $julien = $this->userLoader->createUser('Julien', [], ['ROLE_USER', 'ROLE_WITHOUT_EDIT_ROLE']);

        $julia = $this->userProvider->loadUserByIdentifier('julia');

        $this->updateUser($julia->getId(), [
            'first_name' => 'julia',
            'last_name' => 'julia',
            'roles'=> ['ROLE_WITHOUT_EDIT_ROLE'],
        ]);


        $this->callApiRoute(
            client: $this->client,
            route: 'pim_user_user_rest_post',
            routeArguments: [
                'identifier' => (string) $julien->getId(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => $julien->getUserIdentifier(),
                'enabled'=> true,
                'username' => $julien->getUserIdentifier(),
                'email'=> 'test@test.fr',
                'name_prefix' => null,
                'first_name'=> $julien->getFirstName(),
                'middle_name' => null,
                'last_name'=> $julien->getLastName(),
                'name_suffix' => null,
                'phone'=> null,
                'image' => null,
                'last_login'=> null,
                'catalog_default_locale'=> 'en_US',
                'user_default_locale' => 'fr_FR',
                'catalog_default_scope'=> 'ecommerce',
                'default_category_tree' => $julien->getDefaultTree()->getCode(),
                'email_notifications'=> false,
                'timezone' => 'Africa/Djibouti',
                'groups'=> ['Redactor'],
                'visible_group_ids' => [],
                'roles'=> ['ROLE_WITHOUT_EDIT_ROLE'],
                'product_grid_filters' => [],
                'profile'=> null,
                'avatar' => [
                    'filePath'=> null,
                    'originalFilename' => null
                ],
                'properties' => []
            ]),
        );

        $expectedContent = <<<JSON
        [
            {
                "path": "roles",
                "message": "This user is the last with edit role privileges",
                "global": false
            }
        ]
        JSON;
        $responseJson = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $responseJson->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $responseJson->getContent());
    }

    public function testItThrowsPasswordErrors(): void
    {
        $user = $this->userLoader->createUser('Julien', [], ['ROLE_USER']);

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_user_user_rest_post',
            routeArguments: [
                'identifier' => (string) $user->getId(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'current_password' => 'FalseJulien',
                'new_password' => '1234',
                'new_password_repeat' => 'otherJulien'
            ]),
        );

        $expectedContent = [
            [
                'path' => 'current_password',
                'message'=> 'Wrong password',
                'global' => false
            ],
            [
                'path' => 'new_password',
                'message'=> 'Password must contain at least 8 characters',
                'global' => false
            ],
            [
                'path' => 'new_password_repeat',
                'message'=> 'Passwords do not match',
                'global' => false
            ]
        ];
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());
    }

    public function testItTryToForcePasswordUpdateWithNullNewPasswordsPropertyFailed(): void
    {
        $user = $this->userLoader->createUser('Julien', [], ['ROLE_USER']);

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_user_user_rest_post',
            routeArguments: [
                'identifier' => (string) $user->getId(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'password' => 'tryJulien',
                'current_password' => null,
                'new_password' => null,
                'new_password_repeat' => null
            ]),
        );

        $expectedContent = [
            'code'=> $user->getUserIdentifier(),
            'username'=> $user->getUserIdentifier(),
            'email' => 'Julien@example.com',
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ];

        $responseJson = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $responseJson->getStatusCode());
        $response = json_decode($responseJson->getContent(), true);
        $this->assertEquals($expectedContent['code'], $response['code']);
        $this->assertEquals($expectedContent['email'], $response['email']);
        $this->assertEquals($expectedContent['username'], $response['username']);
        $this->assertEquals($expectedContent['first_name'], $response['first_name']);
        $this->assertEquals($expectedContent['last_name'], $response['last_name']);
    }

    public function testItTryToForcePasswordUpdateFailed(): void
    {
        $user = $this->userLoader->createUser('Julien', [], ['ROLE_USER']);

        $tryPassword = 'newJulien';

        $this->assertFalse($this->userPasswordHasher->isPasswordValid($user, $tryPassword));
        $this->assertTrue($this->userPasswordHasher->isPasswordValid($user, 'Julien'));

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_user_user_rest_post',
            routeArguments: [
                'identifier' => (string) $user->getId(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'password' => $tryPassword,
            ]),
        );

        $expectedContent = [
            'code' => $user->getUserIdentifier(),
            'username' => $user->getUserIdentifier(),
            'email'=> $user->getEmail(),
            'first_name'=> $user->getFirstName(),
            'last_name'=> $user->getLastName(),
        ];
        $responseJson = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $responseJson->getStatusCode());
        $response = json_decode($responseJson->getContent(), true);
        $this->assertEquals($expectedContent['code'], $response['code']);
        $this->assertEquals($expectedContent['email'], $response['email']);
        $this->assertEquals($expectedContent['username'], $response['username']);
        $this->assertEquals($expectedContent['first_name'], $response['first_name']);
        $this->assertEquals($expectedContent['last_name'], $response['last_name']);

        $this->assertFalse($this->userPasswordHasher->isPasswordValid($user, $tryPassword));
        $this->assertTrue($this->userPasswordHasher->isPasswordValid($user, 'Julien'));
    }


    private function updateUser(int $identifier, array $data): void
    {
        $this->updateUserCommandHandler->handle(
            new UpdateUserCommand($identifier, $data)
        );
    }

    private function deleteUser(string $username): void
    {
        $this->deleteUserHandler->handle(
            new DeleteUserCommand($username)
        );
    }

    private function createRoleWithAcls(string $roleCode, array $acls): void
    {
        $role = $this->roleFactory->create();
        $role->setRole($roleCode);
        $role->setLabel($roleCode);
        $this->roleSaver->save($role);

        $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        assert(null !== $roleWithPermissions);

        $permissions = $roleWithPermissions->permissions();
        foreach ($acls as $acl) {
            $permissions[$acl] = true;
        }
        $roleWithPermissions->setPermissions($permissions);

        $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $this->aclManager->flush();
        $this->aclManager->clearCache();
        $this->cacheClearer->clear();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
