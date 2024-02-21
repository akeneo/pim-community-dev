<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserPasswordIntegration extends ControllerIntegrationTestCase
{
    public function test_it_can_not_create_a_user_with_password_less_than_8_characters(): void
    {
        $params = [
            'username' => 'test2',
            'password' => '2short',
            'password_repeat' => '2short',
            'first_name' => 'first',
            'last_name' => 'last',
            'email' => 'new@example.com',
        ];

        $this->logIn('admin');
        $response = $this->callRoute(
            'pim_user_user_rest_create',
            [],
            Request::METHOD_POST,
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            [],
            \json_encode($params)
        );

        $expectedResponse = <<<JSON
{
    "values": [
        {
            "path": "password",
            "message": "Password must contain at least 8 characters",
            "global": false
        }
    ]
}
JSON;

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        self::assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function test_it_can_not_create_a_user_with_password_less_than_8_special_characters(): void
    {
        $params = [
            'username' => 'test2',
            'password' => 'ééééééé',
            'password_repeat' => 'ééééééé',
            'first_name' => 'first',
            'last_name' => 'last',
            'email' => 'new@example.com',
        ];

        $this->logIn('admin');
        $response = $this->callRoute(
            'pim_user_user_rest_create',
            [],
            Request::METHOD_POST,
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            [],
            \json_encode($params)
        );

        $expectedResponse = <<<JSON
{
    "values": [
        {
            "path": "password",
            "message": "Password must contain at least 8 characters",
            "global": false
        }
    ]
}
JSON;

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        self::assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function test_it_can_not_create_a_user_with_password_more_than_4096_characters(): void
    {
        $params = [
            'username' => 'test2',
            'password' => str_repeat('a', 4097),
            'password_repeat' => str_repeat('a', 4097),
            'first_name' => 'first',
            'last_name' => 'last',
            'email' => 'new@example.com',
        ];

        $this->logIn('admin');
        $response = $this->callRoute(
            'pim_user_user_rest_create',
            [],
            Request::METHOD_POST,
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            [],
            \json_encode($params)
        );

        $expectedResponse = <<<JSON
{
    "values": [
        {
            "path": "password",
            "message": "Password must contain less than 4096 characters",
            "global": false
        }
    ]
}
JSON;

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        self::assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function test_it_can_not_create_a_user_with_password_mismatch(): void
    {
        $params = [
            'username' => 'test2',
            'password' => 'thisShouldBeALongEnoughPassword',
            'password_repeat' => 'thisAlsoShouldBeALongEnoughPasswordButDifferent',
            'first_name' => 'first',
            'last_name' => 'last',
            'email' => 'new@example.com',
        ];

        $this->logIn('admin');
        $response = $this->callRoute(
            'pim_user_user_rest_create',
            [],
            Request::METHOD_POST,
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            [],
            \json_encode($params)
        );

        $expectedResponse = <<<JSON
{
    "values": [
        {
            "path": "password_repeat",
            "message": "Passwords do not match",
            "global": false
        }
    ]
}
JSON;

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        self::assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function test_it_can_still_login_existing_users_with_short_password(): void
    {
        $this->createUser('Short_User', '2short');
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        $response = $this->callRoute(
            'pim_user_security_check',
            [],
            Request::METHOD_POST,
            [],
            [
                '_username' => 'Short_User',
                '_password' => '2short',
                '_submit' => '',
                '_target_path' => '',
                '_csrf_token' => $csrfToken
            ]
        );

        $expectedRoute = $this->router->generate('oro_default', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->assertStatusCode($response, Response::HTTP_FOUND);
        $this->assertEquals($expectedRoute, $response->getTargetUrl());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
