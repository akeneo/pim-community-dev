<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ScopeFilterInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\AppAuthenticationUserProvider;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
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
        private AppAuthenticationUserProvider $appAuthenticationUserProvider,
        private CreateJsonWebToken $createJsonWebToken,
        private GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
        private ScopeFilterInterface $scopeFilter
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $appId, string $code): array
    {
        $client = $this->getClient($appId);
        $appUser = $this->getAppUser($appId);

        $scopes = $this->scopeFilter->filterAuthorizationScopes(ScopeList::fromScopes($this->getConnectedAppScopesQuery->execute($appId)));
        $jwt = null;

        $authCode = $this->getAuthCode($code);
        if (ScopeList::fromScopeString($authCode->getScope())->hasScopeOpenId()) {
            /** @var UserInterface|mixed */
            $pimUser = $authCode->getData();
            if (false === $pimUser instanceof UserInterface) {
                throw new \LogicException();
            }

            $appAuthenticationUser = $this->appAuthenticationUserProvider->getAppAuthenticationUser($appId, $pimUser->getId());

            $scopes = $scopes->addScopes($appAuthenticationUser->getConsentedAuthenticationScopes()->getScopes());
            $jwt = $this->createJsonWebToken->create($appId, $appAuthenticationUser);
        }

        $token = $this->randomCodeGenerator->generate();
        $scope = $scopes->toScopeString();
        $this->storage->createAccessToken($token, $client, $appUser, null, $scope);
        $this->storage->markAuthCodeAsUsed($code);

        $accessToken = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'scope' => $scope
        ];

        if (null !== $jwt) {
            $accessToken['id_token'] = $jwt;
        }

        return $accessToken;
    }

    private function getClient(string $appId): Client
    {
        $client = $this->clientProvider->findClientByAppId($appId);
        if (null === $client) {
            throw new \InvalidArgumentException('No client found with the given client id.');
        }

        return $client;
    }

    private function getAuthCode(string $code): IOAuth2AuthCode
    {
        /** @var IOAuth2AuthCode|null $authCode */
        $authCode = $this->storage->getAuthCode($code);
        if (null === $authCode) {
            throw new \InvalidArgumentException('Unknown authorization code.');
        }

        return $authCode;
    }

    private function getAppUser(string $appId): UserInterface
    {
        $appConfirmation = $this->appConfirmationQuery->execute($appId);

        /** @var UserInterface|null */
        $appUser = $this->userRepository->find($appConfirmation->getUserId());
        if (null === $appUser) {
            throw new \LogicException();
        }

        return $appUser;
    }
}
