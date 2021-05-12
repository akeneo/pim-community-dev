<?php

namespace Akeneo\Connectivity\AppActivationBundle\Controller;

use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivateController
{
    private ClientManagerInterface $clientManager;
    private Registry $registry;

    public function __construct(
        ClientManagerInterface $clientManager,
        Registry $registry
    ) {
        $this->clientManager = $clientManager;
        $this->registry = $registry;
    }

    /**
     * @Route("/apps/activate/{id}", name="app_activate")
     */
    public function activate(string $id): Response
    {
        $client = $this->findOrCreateClient($id);

        return new Response('hello world');
    }

    private function findOrCreateClient(string $id): Client
    {
        $repo = $this->registry->getRepository(Client::class);
        $client = $repo->findOneBy(['randomId' => $id]);

        if (null !== $client) {
            return $client;
        }

        /** @var Client $client */
        $client = $this->clientManager->createClient();
        $client->setRandomId($id);
        $client->setLabel(sprintf('Fake extension #%s', $id));
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);

        $this->clientManager->updateClient($client);

        return $client;
    }
}
