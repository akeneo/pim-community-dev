<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ClientManager implements ClientManagerInterface
{
    private ClientManagerInterface $baseClientManager;

    public function __construct(ClientManagerInterface $baseClientManager)
    {
        $this->baseClientManager = $baseClientManager;
    }
    public function findClientByPublicId($publicId)
    {
        if (false === $pos = strpos($publicId, '_')) {
            return;
        }

        $randomId = substr($publicId, $pos + 1);

        return $this->findClientBy(['randomId' => $randomId]);
    }

    public function deleteClient(ClientInterface $client)
    {
        return $this->baseClientManager->deleteClient($client);
    }

    public function findClientBy(array $criteria)
    {
        return $this->baseClientManager->findClientBy($criteria);
    }

    public function getClass()
    {
        return $this->baseClientManager->getClass();
    }

    public function updateClient(ClientInterface $client)
    {
        return $this->baseClientManager->updateClient($client);
    }

    public function createClient()
    {
        return $this->baseClientManager->createClient();
    }
}
