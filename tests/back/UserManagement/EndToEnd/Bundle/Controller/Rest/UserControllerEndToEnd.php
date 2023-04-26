<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\EndToEnd\Bundle\Controller\Rest;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidatorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\UserInterface;
use AkeneoTest\UserManagement\Helper\ControllerIntegrationTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerEndToEnd extends ControllerIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->logAs('julia');
    }

    public function testItUpdatePassword(): void
    {
        $user = $this->createUser([
            'username' => 'Julien',
            'first_name' => 'Julien',
            'last_name' => 'Julien',
            'email' => 'Julien@akeneo.com',
            'password' => 'Julien',
            'default_category_tree' => 'master',
        ]);

        $newPassword = 'newJulien';
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
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());

        $this->assertFalse($this->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'Julien'));
        $this->assertTrue($this->get(UserPasswordHasherInterface::class)->isPasswordValid($user, $newPassword));
    }

    public function testItUpdateUser(): void
    {
        $user = $this->createUser([
            'username' => 'Julien',
            'first_name' => 'Julien',
            'last_name' => 'Julien',
            'email' => 'Julien@akeneo.com',
            'password' => 'Julien',
            'default_category_tree' => 'master',
        ]);

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
                'email'=> $user->getEmail(),
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
                'properties' => [
                    'proposals_state_notifications' => false,
                    'proposals_to_review_notification' => true,
                ]
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
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());
    }

    public function testItThrowsWrongPasswordError(): void
    {
        $user = $this->createUser([
            'username' => 'Julien',
            'first_name' => 'Julien',
            'last_name' => 'Julien',
            'email' => 'Julien@akeneo.com',
            'password' => 'Julien',
            'default_category_tree' => 'master',
        ]);

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
                'new_password' => $newPassword,
                'new_password_repeat' => $newPassword
            ]),
        );

        $expectedContent = [
            [
                'path' => "current_password",
                'message'=> "Wrong password",
                'global' => false
            ]
        ];
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());
    }

    public function testItThrowsPasswordDoesNotMatch(): void
    {
        $user = $this->createUser([
            'username' => 'Julien',
            'first_name' => 'Julien',
            'last_name' => 'Julien',
            'email' => 'Julien@akeneo.com',
            'password' => 'Julien',
            'default_category_tree' => 'master',
        ]);

        $newPassword = 'newJulien';
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
                'new_password_repeat' => 'otherJulien'
            ]),
        );

        $expectedContent = [
            [
                'path'=> "new_password_repeat",
                'message' => "Passwords do not match",
                'global'=> false
            ]
        ];
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());
    }

    public function testItTryToForcePasswordUpdateWithNullNewPasswordsPropertyFailed(): void
    {
        $user = $this->createUser([
            'username' => 'Julien',
            'first_name' => 'Julien',
            'last_name' => 'Julien',
            'email' => 'Julien@akeneo.com',
            'password' => 'Julien',
            'default_category_tree' => 'master',
        ]);

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
        $user = $this->createUser([
            'username' => 'Julien',
            'first_name' => 'Julien',
            'last_name' => 'Julien',
            'email' => 'Julien@akeneo.com',
            'password' => 'Julien',
            'default_category_tree' => 'master',
        ]);

        $tryPassword = 'newJulien';
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
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());

        $this->assertFalse($this->get('security.user_password_hasher')->isPasswordValid($user, $tryPassword));
        $this->assertTrue($this->get('security.user_password_hasher')->isPasswordValid($user, 'Julien'));
    }

    private function createUser(array $data): UserInterface
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->get('pim_user.factory.user');
        /** @var ObjectUpdaterInterface $userUpdater */
        $userUpdater = $this->get('pim_user.updater.user');
        /** @var ValidatorInterface $validator */
        $validator = $this->get('validator');
        /** @var SaverInterface $validator */
        $userSaver = $this->get('pim_user.saver.user');

        $user = $userFactory->create();
        $userUpdater->update($user, $data);

        $violations = $validator->validate($user);
        if (count($violations) > 0) {
            throw new \InvalidArgumentException((string)$violations);
        }

        $userSaver->save($user);

        return $user;
    }
    
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
