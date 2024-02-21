<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientProvider implements ClientProviderInterface
{
    public function __construct(private ClientManagerInterface $clientManager)
    {
    }

    public function findOrCreateClient(App $app): Client
    {
        $client = $this->findClientByAppId($app->getId());

        if ($client === null) {
            $client = $this->clientManager->createClient();
            if (!$client instanceof Client) {
                throw new \LogicException(
                    \sprintf('Expected instance of %s, got %s', Client::class, \get_debug_type($client))
                );
            }

            $client->setRedirectUris([$app->getCallbackUrl()]);
            $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]);
            $client->setMarketplacePublicAppId($app->getId());

            $this->clientManager->updateClient($client);
        }

        return $client;
    }

    public function findClientByAppId(string $appId): ?Client
    {
        $client = $this->clientManager->findClientBy(['marketplacePublicAppId' => $appId]);

        if (null !== $client && !$client instanceof Client) {
            throw new \LogicException(
                \sprintf('Expected null or instance of %s, got %s', Client::class, \get_debug_type($client))
            );
        }

        return $client;
    }
}
