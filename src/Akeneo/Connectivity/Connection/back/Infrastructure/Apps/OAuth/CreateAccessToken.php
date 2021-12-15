<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetConnectedAppScopesQueryInterface;
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
    private IOAuth2GrantCode $storage;
    private ClientProviderInterface $clientProvider;
    private RandomCodeGeneratorInterface $randomCodeGenerator;
    private GetAppConfirmationQueryInterface $appConfirmationQuery;
    private UserRepositoryInterface $userRepository;
    private AppAuthenticationUserProvider $appAuthenticationUserProvider;
    private CreateJsonWebToken $createJsonWebToken;
    private GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery;

    public function __construct(
        IOAuth2GrantCode $storage,
        ClientProviderInterface $clientProvider,
        RandomCodeGeneratorInterface $randomCodeGenerator,
        GetAppConfirmationQueryInterface $appConfirmationQuery,
        UserRepositoryInterface $userRepository,
        AppAuthenticationUserProvider $appAuthenticationUserProvider,
        CreateJsonWebToken $createJsonWebToken,
        GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
    ) {
        $this->storage = $storage;
        $this->clientProvider = $clientProvider;
        $this->randomCodeGenerator = $randomCodeGenerator;
        $this->appConfirmationQuery = $appConfirmationQuery;
        $this->userRepository = $userRepository;
        $this->appAuthenticationUserProvider = $appAuthenticationUserProvider;
        $this->createJsonWebToken = $createJsonWebToken;
        $this->getConnectedAppScopesQuery = $getConnectedAppScopesQuery;
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

        $token = $this->randomCodeGenerator->generate();

        $authorizationScopesList = ScopeList::fromScopes($this->getConnectedAppScopesQuery->execute($appId));

        $appUser = $this->getAppUser($appId);
        /* @phpstan-ignore-next-line */
        $this->storage->createAccessToken($token, $client, $appUser, null, $authorizationScopesList->toScopeString());
        $this->storage->markAuthCodeAsUsed($code);

        $accessToken = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'scope' => $authorizationScopesList->toScopeString()
        ];

        $accessToken = $this->appendOpenIdData($authCode, $appId, $accessToken);

        return $accessToken;
    }

    private function getAppUser(string $appId): UserInterface
    {
        $appConfirmation = $this->appConfirmationQuery->execute($appId);
        $appUserId = $appConfirmation->getUserId();

        /** @var UserInterface|null */
        $appUser = $this->userRepository->find($appUserId);
        if (null === $appUser) {
            throw new \LogicException(sprintf('User %s not found', $appUserId));
        }

        return $appUser;
    }

    private function appendOpenIdData(IOAuth2AuthCode $authCode, string $appId, array $accessToken): array
    {
        /** @var UserInterface|mixed */
        $pimUser = $authCode->getData();
        if (false === $pimUser instanceof UserInterface) {
            throw new \LogicException();
        }

        $appAuthenticationUser = $this->appAuthenticationUserProvider->getAppAuthenticationUser($appId, $pimUser->getId());
        $authenticationScopes = $appAuthenticationUser->getConsentedAuthenticationScopes();

        if($authenticationScopes->hasScope(AuthenticationScope::SCOPE_OPENID))
        {
            $accessToken['id_token'] = $this->createJsonWebToken->create($appId, $appAuthenticationUser);

            $existingScopesList = ScopeList::fromScopeString($accessToken['scope']);
            $newScopeList = $existingScopesList->addScopes($authenticationScopes);
            $accessToken['scope'] = $newScopeList->toScopeString();
        }

        return $accessToken;
    }
}
