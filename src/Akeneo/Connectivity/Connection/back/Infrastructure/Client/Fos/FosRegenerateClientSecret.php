<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateClientSecret;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Doctrine\DBAL\Driver\Connection as DbalConnection;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Util\Random;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FosRegenerateClientSecret implements RegenerateClientSecret
{
    /** @var ClientManagerInterface */
    private $clientManager;

    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(ClientManagerInterface $clientManager, DbalConnection $dbalConnection)
    {
        $this->clientManager = $clientManager;
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(ClientId $clientId): void
    {
        $fosClient = $this->findClient($clientId);
        $fosClient->setSecret(Random::generateToken());
        $this->clientManager->updateClient($fosClient);

        $this->deleteApiToken($clientId);
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

    private function deleteApiToken(ClientId $clientId)
    {
        $deleteSqlAccessToken = <<<SQL
DELETE FROM pim_api_access_token WHERE client = :client_id
SQL;
        $stmt = $this->dbalConnection->prepare($deleteSqlAccessToken);
        $stmt->execute(['client_id' => $clientId->id()]);

        $deleteSqlRefreshToken = <<<SQL
DELETE FROM pim_api_refresh_token WHERE client = :client_id
SQL;
        $stmt = $this->dbalConnection->prepare($deleteSqlRefreshToken);
        $stmt->execute(['client_id' => $clientId->id()]);
    }
}
