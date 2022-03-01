<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAccessTokenQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationUuidQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2AuthCode;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAccessToken implements CreateAccessTokenInterface
{
    public function __construct(
        private IOAuth2GrantCode $storage,
        private ClientProviderInterface $clientProvider,
        private RandomCodeGeneratorInterface $randomCodeGenerator,
        private GetAppConfirmationQueryInterface $appConfirmationQuery,
        private UserRepositoryInterface $userRepository,
        private CreateJsonWebToken $createJsonWebToken,
        private GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
        private GetUserConsentedAuthenticationUuidQueryInterface $getUserConsentedAuthenticationUuidQuery,
        private GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        private GetAccessTokenQueryInterface $getAccessTokenQuery,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $clientId, string $code): array
    {
        $client = $this->clientProvider->findClientByAppId($clientId);
        if (null === $client) {
            throw new \InvalidArgumentException('No client found with the given client id.');
        }

        /** @var IOAuth2AuthCode|null $authCode */
        $authCode = $this->storage->getAuthCode($code);
        if (null === $authCode) {
            throw new \InvalidArgumentException('Unknown authorization code.');
        }

        $scopes = $this->getConnectedAppScopesQuery->execute($clientId);
        $authorizationScopesList = ScopeList::fromScopes($scopes);

        if (null === $token = $this->getAccessTokenQuery->execute($clientId, $scopes)) {
            $token = $this->randomCodeGenerator->generate();

            $appUser = $this->getAppUser($clientId);
            /* @phpstan-ignore-next-line */
            $this->storage->createAccessToken($token, $client, $appUser, null, $authorizationScopesList->toScopeString());
        }

        $this->storage->markAuthCodeAsUsed($code);

        $accessToken = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'scope' => $authorizationScopesList->toScopeString()
        ];

        return $this->appendOpenIdData($authCode, $clientId, $accessToken);
    }

    private function getAppUser(string $clientId): UserInterface
    {
        $appConfirmation = $this->appConfirmationQuery->execute($clientId);
        $appUserId = $appConfirmation->getUserId();

        /** @var UserInterface|null */
        $appUser = $this->userRepository->find($appUserId);
        if (null === $appUser) {
            throw new \LogicException(\sprintf('User %s not found', $appUserId));
        }

        return $appUser;
    }

    private function appendOpenIdData(IOAuth2AuthCode $authCode, string $clientId, array $accessToken): array
    {
        /** @var UserInterface|mixed */
        $pimUser = $authCode->getData();
        if (false === $pimUser instanceof UserInterface) {
            throw new \LogicException();
        }

        $authenticationScopes = ScopeList::fromScopes(
            $this->getUserConsentedAuthenticationScopesQuery->execute($pimUser->getId(), $clientId)
        );

        if ($authenticationScopes->hasScope(AuthenticationScope::SCOPE_OPENID)) {
            $ppid = $this->getUserConsentedAuthenticationUuidQuery->execute($pimUser->getId(), $clientId);
            $accessToken['id_token'] = $this->createJsonWebToken->create(
                $clientId,
                $ppid,
                $authenticationScopes,
                $pimUser->getFirstName(),
                $pimUser->getLastName(),
                $pimUser->getEmail()
            );

            $existingScopesList = ScopeList::fromScopeString($accessToken['scope']);
            $newScopeList = $existingScopesList->addScopes($authenticationScopes);
            $accessToken['scope'] = $newScopeList->toScopeString();
        }

        return $accessToken;
    }
}
