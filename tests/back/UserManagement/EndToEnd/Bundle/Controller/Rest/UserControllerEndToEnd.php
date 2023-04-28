<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\EndToEnd\Bundle\Controller\Rest;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\UserManagement\Helper\ControllerEndToEndTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerEndToEnd extends ControllerEndToEndTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->logAs('julia');
    }

    public function testItUpdatePassword(): void
    {
        $user = $this->getUserLoader()->createUser('Julien', [], ['ROLE_USER']);

        $newPassword = 'newJulien';

        $this->assertTrue($this->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'Julien'));
        $this->assertFalse($this->get(UserPasswordHasherInterface::class)->isPasswordValid($user, $newPassword));

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
            'enabled' => true,
            'username'=> $user->getUserIdentifier(),
            'email' => $user->getEmail(),
            'name_prefix'=> null,
            'first_name' => $user->getFirstName(),
            'middle_name'=> null,
            'last_name' => $user->getLastName(),
            'name_suffix'=> null,
            'phone' => null,
            'image'=> null,
            'last_login' => null,
            'login_count'=> 0,
            'catalog_default_locale' => "en_US",
            'user_default_locale'=> "en_US",
            'catalog_default_scope' => "ecommerce",
            'default_category_tree'=> $user->getDefaultTree()->getCode(),
            'email_notifications' => false,
            'timezone'=> "UTC",
            'groups' => ["All"],
            'visible_group_ids'=> [],
            'roles' => ["ROLE_USER"],
            'product_grid_filters'=> [],
            'profile' => null,
            'avatar'=> [
                'filePath' => null,
                'originalFilename'=> null
            ],
            'meta' => [
                'id'=> $user->getId(),
                'created' => $user->getCreatedAt()->getTimestamp(),
                'updated'=> $user->getUpdatedAt()->getTimestamp(),
                'form' => "pim-user-edit-form",
                'image'=> [
                    'filePath' => null
                ],
            ],
            'properties'=> []
        ];
        $responseJson = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $responseJson->getStatusCode());
        $response = json_decode($responseJson->getContent(), true);
        $this->assertEquals($expectedContent['code'], $response['code']);
        $this->assertEquals($expectedContent['email'], $response['email']);
        $this->assertEquals($expectedContent['username'], $response['username']);
        $this->assertEquals($expectedContent['first_name'], $response['first_name']);
        $this->assertEquals($expectedContent['last_name'], $response['last_name']);

        $this->assertFalse($this->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'Julien'));
        $this->assertTrue($this->get(UserPasswordHasherInterface::class)->isPasswordValid($user, $newPassword));
    }

    public function testItUpdateUser(): void
    {
        $user = $this->getUserLoader()->createUser('Julien', [], ['ROLE_USER']);

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
                'catalog_default_locale'=> "en_US",
                'user_default_locale' => "fr_FR",
                'catalog_default_scope'=> "ecommerce",
                'default_category_tree' => $user->getDefaultTree()->getCode(),
                'email_notifications'=> false,
                'timezone' => "Africa/Djibouti",
                'groups'=> ["Redactor"],
                'visible_group_ids' => [],
                'roles'=> ["ROLE_USER", "ROLE_CATALOG_MANAGER"],
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
            'enabled' => true,
            'username'=> $user->getUserIdentifier(),
            'email' => 'test@test.fr',
            'name_prefix'=> null,
            'first_name' => $user->getFirstName(),
            'middle_name'=> null,
            'last_name' => $user->getLastName(),
            'name_suffix'=> null,
            'phone' => null,
            'image'=> null,
            'last_login' => null,
            'login_count'=> 0,
            'catalog_default_locale' => "en_US",
            'user_default_locale'=> "fr_FR",
            'catalog_default_scope' => "ecommerce",
            'default_category_tree'=> $user->getDefaultTree()->getCode(),
            'email_notifications' => false,
            'timezone'=> "Africa/Djibouti",
            'groups' => ["Redactor", "All"],
            'visible_group_ids'=> [3],
            'roles' => ['ROLE_USER', 'ROLE_CATALOG_MANAGER'],
            'product_grid_filters'=> [],
            'profile' => null,
            'avatar'=> [
                'filePath' => null,
                'originalFilename'=> null
            ],
            'meta' => [
                'id'=> $user->getId(),
                'created' => $user->getCreatedAt()->getTimestamp(),
                'updated'=> $user->getUpdatedAt()->getTimestamp(),
                'form' => "pim-user-edit-form",
                'image'=> [
                    'filePath' => null
                ],
            ],
            'properties'=> []
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

    public function testItThrowsPasswordErrors(): void
    {
        $user = $this->getUserLoader()->createUser('Julien', [], ['ROLE_USER']);

        $newPassword = 'newJulien';
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
                'path' => "current_password",
                'message'=> "Wrong password",
                'global' => false
            ],
            [
                'path' => "new_password",
                'message'=> "Password must contain at least 8 characters",
                'global' => false
            ],
            [
                'path' => "new_password_repeat",
                'message'=> "Passwords do not match",
                'global' => false
            ]
        ];
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());
    }

    public function testItTryToForcePasswordUpdateWithNullNewPasswordsPropertyFailed(): void
    {
        $user = $this->getUserLoader()->createUser('Julien', [], ['ROLE_USER']);

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
            [
                'path' => "current_password",
                'message'=> "Wrong password",
                'global' => false
            ],
            [
                'path'=> "new_password",
                'message' => "Password must contain at least 8 characters",
                'global'=> false
            ]
        ];

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());
    }

    public function testItTryToForcePasswordUpdateFailed(): void
    {
        $user = $this->getUserLoader()->createUser('Julien', [], ['ROLE_USER']);

        $tryPassword = 'newJulien';

        $this->assertFalse($this->get('security.user_password_hasher')->isPasswordValid($user, $tryPassword));
        $this->assertTrue($this->get('security.user_password_hasher')->isPasswordValid($user, 'Julien'));

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
            'enabled'=> true,
            'username' => $user->getUserIdentifier(),
            'email'=> $user->getEmail(),
            'name_prefix' => null,
            'first_name'=> $user->getFirstName(),
            'middle_name' => null,
            'last_name'=> $user->getLastName(),
            'name_suffix' => null,
            'phone'=> null,
            'image' => null,
            'last_login'=> null,
            'login_count' => 0,
            'catalog_default_locale'=> "en_US",
            'user_default_locale' => "en_US",
            'catalog_default_scope'=> "ecommerce",
            'default_category_tree' => $user->getDefaultTree()->getCode(),
            'email_notifications'=> false,
            'timezone' => "UTC",
            'groups'=> ["All"],
            'visible_group_ids' => [],
            'roles'=> ["ROLE_USER"],
            'product_grid_filters' => [],
            'profile'=> null,
            'avatar' => [
                'filePath'=> null,
                'originalFilename' => null
            ],
            'meta'=> [
                'id' => $user->getId(),
                'created'=> $user->getCreatedAt()->getTimestamp(),
                'updated' => $user->getUpdatedAt()->getTimestamp(),
                'form'=> "pim-user-edit-form",
                'image' => [
                    'filePath'=> null
                ],
            ],
            'properties' => []
        ];
        $responseJson = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $responseJson->getStatusCode());
        $response = json_decode($responseJson->getContent(), true);
        $this->assertEquals($expectedContent['code'], $response['code']);
        $this->assertEquals($expectedContent['email'], $response['email']);
        $this->assertEquals($expectedContent['username'], $response['username']);
        $this->assertEquals($expectedContent['first_name'], $response['first_name']);
        $this->assertEquals($expectedContent['last_name'], $response['last_name']);

        $this->assertFalse($this->get('security.user_password_hasher')->isPasswordValid($user, $tryPassword));
        $this->assertTrue($this->get('security.user_password_hasher')->isPasswordValid($user, 'Julien'));
    }

    private function getUserLoader(): UserLoader {
        return $this->get(UserLoader::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
