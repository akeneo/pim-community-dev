<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientProvider implements ClientProviderInterface
{
    private ClientManagerInterface $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    public function findOrCreateClient($app): Client
    {
        $appId = $app->getId();

        $client = $this->clientManager->findClientBy(['marketplace_public_app_id' => $appId]);
        if ($client === null) {
            /** @var Client $client */
            $client = $this->clientManager->createClient();

            $client->setRedirectUris([$app->callbackUrl()]);
            $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]); //ToDO test grant type implicit
            $client->setMarketplacePublicAppId($appId);

            $this->clientManager->updateClient($client);
        }

        return $client;
    }
}
