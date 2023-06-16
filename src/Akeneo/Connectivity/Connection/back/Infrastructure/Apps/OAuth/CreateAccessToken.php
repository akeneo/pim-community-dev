<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\DeleteAccessTokensQueryInterface;
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
        private DeleteAccessTokensQueryInterface $deleteAccessTokensQuery,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $appId, string $code): array
    {
        $client = $this->clientProvider->findClientByAppId($appId);
        if (null === $client) {
            throw new \InvalidArgumentException('No client found with the given client id.');
        }

        /** @var IOAuth2AuthCode|null $authCode */
        $authCode = $this->storage->getAuthCode($code);
        if (null === $authCode) {
            throw new \InvalidArgumentException('Unknown authorization code.');
        }

        $authorizationScopesList = ScopeList::fromScopes($this->getConnectedAppScopesQuery->execute($appId));
        $scopeString = $authorizationScopesList->toScopeString();

        if (null === $token = $this->getAccessTokenQuery->execute($appId, $scopeString)) {
            $this->deleteAccessTokensQuery->execute($appId);

            $token = $this->randomCodeGenerator->generate();

            $appUser = $this->getAppUser($appId);
            /* @phpstan-ignore-next-line */
            $this->storage->createAccessToken($token, $client, $appUser, null, $scopeString);
        }

        $this->storage->markAuthCodeAsUsed($code);

        $accessToken = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'scope' => $scopeString
        ];

        return $this->appendOpenIdData($authCode, $appId, $accessToken);
    }

    private function getAppUser(string $appId): UserInterface
    {
        $appConfirmation = $this->appConfirmationQuery->execute($appId);
        $appUserId = $appConfirmation->getUserId();

        /** @var UserInterface|null */
        $appUser = $this->userRepository->find($appUserId);
        if (null === $appUser) {
            throw new \LogicException(\sprintf('User %s not found', $appUserId));
        }

        return $appUser;
    }

    private function appendOpenIdData(IOAuth2AuthCode $authCode, string $appId, array $accessToken): array
    {
        /** @var UserInterface|mixed */
        $pimUser = $authCode->getData();
        if (!$pimUser instanceof UserInterface) {
            throw new \LogicException();
        }

        $authenticationScopes = ScopeList::fromScopes(
            $this->getUserConsentedAuthenticationScopesQuery->execute($pimUser->getId(), $appId)
        );

        if ($authenticationScopes->hasScope(AuthenticationScope::SCOPE_OPENID)) {
            $ppid = $this->getUserConsentedAuthenticationUuidQuery->execute($pimUser->getId(), $appId);
            $accessToken['id_token'] = $this->createJsonWebToken->create(
                $appId,
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
