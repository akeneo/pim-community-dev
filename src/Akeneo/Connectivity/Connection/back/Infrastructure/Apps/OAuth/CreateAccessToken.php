<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetConnectedAppScopesQueryInterface;
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
    private GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery;

    public function __construct(
        IOAuth2GrantCode $storage,
        ClientProviderInterface $clientProvider,
        RandomCodeGeneratorInterface $randomCodeGenerator,
        GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery
    ) {
        $this->storage = $storage;
        $this->clientProvider = $clientProvider;
        $this->randomCodeGenerator = $randomCodeGenerator;
        $this->getConnectedAppScopesQuery = $getConnectedAppScopesQuery;
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

        $token = $this->randomCodeGenerator->generate();

        /* @phpstan-ignore-next-line */
        $this->storage->createAccessToken($token, $client, $authCode->getData(), null);

        $this->storage->markAuthCodeAsUsed($code);

        $scopes = $this->getConnectedAppScopesQuery->execute($clientId);

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'scope' => \implode(' ', $scopes),
        ];
    }
}
