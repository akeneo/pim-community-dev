<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Fixtures;

use Akeneo\Apps\Domain\Model\Read\Client;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use Akeneo\Apps\Infrastructure\Client\Fos\CreateClient;
use Akeneo\Apps\Infrastructure\User\Internal\CreateUser;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLoader
{
    /** @var Connection */
    private $dbalConnection;
    /** @var CreateClient */
    private $createClient;
    /** @var CreateUser */
    private $createUser;

    public function __construct(Connection $dbalConnection, CreateClient $createClient, CreateUser $createUser, AppRepository $repository)
    {
        $this->dbalConnection = $dbalConnection;
        $this->createClient = $createClient;
        $this->createUser = $createUser;
    }

    public function createApp(string $code, string $label, string $flowType)
    {
        $client = $this->createClient($label);
        $userId = $this->createUser($code, $label);

        $insertSql = <<<SQL
    INSERT INTO akeneo_app (client_id, user_id, code, label, flow_type)
    VALUES (:client_id, :user_id, :code, :label, :flow_type)
SQL;

        $this->dbalConnection->executeQuery(
            $insertSql,
            [
                'client_id' => $client->id(),
                'user_id' => $userId->id(),
                'code' => $code,
                'label' => $label,
                'flow_type' => $flowType
            ]
        );
    }

    public function createClient(string $label): Client
    {
        return $this->createClient->execute($label);
    }

    public function createUser(string $username, string $firstname): UserId
    {
        return $this
            ->createUser
            ->execute($username, $firstname, 'APP', $username, sprintf('%s@email.com', $username));
    }
}
