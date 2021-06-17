<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\AkeneoConnect;

use Akeneo\FreeTrial\Domain\API\InviteUserAPI;
use Akeneo\FreeTrial\Domain\Exception\InvitationAlreadySentException;
use Akeneo\FreeTrial\Domain\Exception\InvitationFailedException;
use PharIo\Manifest\InvalidEmailException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoConnectInviteUserAPI implements InviteUserAPI
{
    public const INVITATION_ALREADY_SENT = 'invitation_already_sent';
    public const INVALID_EMAIL = 'invalid_email';
    public const INVALID_REQUEST_BODY = 'invalid_request_body';

//    private APIClient $client;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
//        $this->client = $client;
        $this->logger = $logger;
    }

    public function inviteUser(string $email): void
    {
        return;
//        $response = $this->client->inviteUser($email);
        try {
            if ($response->getStatusCode() === Response::HTTP_OK) {
                return;
            }
            $responseContentError = $response->toArray(false);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('Error while calling Akeneo Connect : %s', $e->getMessage()));
            throw new InvitationFailedException();
        }

        $this->handleError($responseContentError ?? []);
    }

    private function handleError(array $responseContentError): void
    {
        if (! isset($responseContentError['error']['code'])) {
            throw new InvitationFailedException();
        }

        switch ($responseContentError['error']['code']) {
            case self::INVITATION_ALREADY_SENT:
                throw new InvitationAlreadySentException();
            case self::INVALID_EMAIL:
                throw new InvalidEmailException();
            case self::INVALID_REQUEST_BODY:
                $this->logger->error('Error while calling Akeneo Connect : invalid request');
                throw new InvitationFailedException();
            default:
                $this->logger->error('Error while calling Akeneo Connect', $responseContentError);
                throw new InvitationFailedException();
        }
    }
}
