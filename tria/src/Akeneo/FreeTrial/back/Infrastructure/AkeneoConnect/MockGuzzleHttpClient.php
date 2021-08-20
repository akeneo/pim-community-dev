<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\AkeneoConnect;

use Akeneo\FreeTrial\Domain\Query\GetInvitedUsersQuery;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MockGuzzleHttpClient implements ClientInterface
{
    private const AUTHORIZED_EMAILS = [
        'Julia@example.com',
        'Peter@example.com',
        'Mary@example.com',
        'Sandra@example.com',
        'Julien@example.com',
    ];

    private GetInvitedUsersQuery $getInvitedUsersQuery;

    public function __construct(GetInvitedUsersQuery $getInvitedUsersQuery)
    {
        $this->getInvitedUsersQuery = $getInvitedUsersQuery;
    }

    public function request($method, $uri, array $options = [])
    {
        if ($method === 'POST' && $uri === APIClient::URI_CONNECT) {
            return $this->buildConnectionResponse();
        }

        if ($method === 'POST' && $uri === APIClient::URI_INVITE_USER) {
            return $this->buildInviteUserResponse($options);
        }

        throw new \Exception(sprintf('No request handled for method "%s" on URI "%s"', $method, $uri));
    }

    public function send(RequestInterface $request, array $options = [])
    {
        throw new \Exception('The method "send" is not implemented for the fake client.');
    }

    public function sendAsync(RequestInterface $request, array $options = [])
    {
        throw new \Exception('The method "sendAsync" is not implemented for the fake client.');
    }

    public function requestAsync($method, $uri, array $options = [])
    {
        throw new \Exception('The method "requestAsync" is not implemented for the fake client.');
    }

    public function getConfig($option = null)
    {
        throw new \Exception('The method "getConfig" is not implemented for the fake client.');
    }

    private function buildConnectionResponse(): ResponseInterface
    {
        return new Response(200, [], '{"access_token": "aequ3RooquaePeesiThei5IoQuo6waiFeech9ooGohe"}');
    }

    private function buildInviteUserResponse(array $options): ResponseInterface
    {
        if (!isset($options['json']['email'])) {
            return new Response(400, [], sprintf('{"error": {"code": "%s"}}', AkeneoConnectInviteUserAPI::INVALID_REQUEST_BODY));
        }

        if ('admin@example.com' === $options['json']['email'] || $this->isEmailAlreadyInvited($options['json']['email'])) {
            return new Response(400, [], sprintf('{"error": {"code": "%s"}}', AkeneoConnectInviteUserAPI::INVITATION_ALREADY_SENT));
        }

        if (in_array($options['json']['email'], self::AUTHORIZED_EMAILS)) {
            return new Response(200, [], '{}');
        }

        return new Response(500, [], sprintf('{"error": {"code": "%s"}}', AkeneoConnectInviteUserAPI::INTERNAL_ERROR));
    }

    private function isEmailAlreadyInvited(string $email): bool
    {
        $invitedUsers = $this->getInvitedUsersQuery->execute();
        foreach ($invitedUsers as $invitedUser) {
            if ($invitedUser->getEmail() === $email) {
                return true;
            }
        }

        return false;
    }
}
