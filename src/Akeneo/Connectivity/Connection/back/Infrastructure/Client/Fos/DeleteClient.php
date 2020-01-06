<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteClient implements DeleteClientInterface
{
    /** @var ClientManagerInterface */
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    public function execute(ClientId $clientId): void
    {
        $fosClient = $this->findClient($clientId);
        $this->clientManager->deleteClient($fosClient);
    }

    private function findClient(ClientId $clientId): Client
    {
        $fosClient = $this->clientManager->findClientBy(['id' => $clientId->id()]);
        if (null === $fosClient) {
            throw new \InvalidArgumentException(
                sprintf('Client with id "%s" not found.', $clientId->id())
            );
        }

        return $fosClient;
    }
}
