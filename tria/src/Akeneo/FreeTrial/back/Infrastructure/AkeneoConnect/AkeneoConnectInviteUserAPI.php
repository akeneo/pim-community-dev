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

use Akeneo\FreeTrial\Domain\API\InviteUserAPI;
use Akeneo\FreeTrial\Domain\Exception\InvalidEmailException;
use Akeneo\FreeTrial\Domain\Exception\InvitationAlreadySentException;
use Akeneo\FreeTrial\Domain\Exception\InvitationFailedException;
use Akeneo\FreeTrial\Domain\Exception\UnauthorizedEmailException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class AkeneoConnectInviteUserAPI implements InviteUserAPI
{
    public const INVITATION_ALREADY_SENT = 'user_is_already_invited_invitation';
    public const INVALID_REQUEST_BODY = 'invalid_request_invitation';
    public const INTERNAL_ERROR = 'internal_server_error_invitation';
    public const UNAUTHORIZED_EMAIL = 'user_has_unauthorized_email_address';

    private APIClient $client;

    private LoggerInterface $logger;

    public function __construct(APIClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function inviteUser(string $email): void
    {
        try {
            $response = $this->client->inviteUser($email);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                return;
            }

            $responseContentError = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('Error while calling Akeneo Connect : %s', $e->getMessage()));
            throw new InvitationFailedException();
        }

        $this->handleError($responseContentError ?? []);
    }

    private function handleError(array $responseContentError): void
    {
        if (!isset($responseContentError['error']['code'])) {
            throw new InvitationFailedException();
        }

        switch ($responseContentError['error']['code']) {
            case self::INVITATION_ALREADY_SENT:
                throw new InvitationAlreadySentException();
            case self::INVALID_REQUEST_BODY:
                $this->logger->error('Error while calling Akeneo Connect : invalid request', $responseContentError);
                throw new InvalidEmailException();
            case self::UNAUTHORIZED_EMAIL:
                throw new UnauthorizedEmailException();
            default:
                $this->logger->error('Error while calling Akeneo Connect', $responseContentError);
                throw new InvitationFailedException();
        }
    }
}
