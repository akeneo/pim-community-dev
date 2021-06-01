<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AppActivationController
{
    private ClientManagerInterface $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    public function __invoke(Request $request, string $identifier)
    {
        $this->findOrCreateClient($identifier);

        return RedirectResponse::create($this->getAppRedirectUrl());
    }

    private function getAppRedirectUrl()
    {
        $pimSource = 'http://172.17.0.1:8080';
        $nativeAppUrl = 'http://172.17.0.1:8081/activate';

        return "${nativeAppUrl}?pim=${pimSource}";
    }

    private function findOrCreateClient(string $identifier): Client
    {
        $client = $this->clientManager->findClientBy(['randomId' =>$identifier]);
        if (null !== $client) {
            return $client;
        }
        $client = $this->clientManager->createClient();
        $client->setRandomId($identifier);
        $client->setRedirectUris( ['http://172.17.0.1:8081/activate']);
        $client->setLabel('yell-extenssion');
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_IMPLICIT]);
        $this->clientManager->updateClient($client);

        return $client;
    }
}
