<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQuery;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\FetchMode;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DbalSelectConnectionWithCredentialsByCodeQuery implements SelectConnectionWithCredentialsByCodeQuery
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $code): ?ConnectionWithCredentials
    {
        $selectSQL = <<<SQL
SELECT
    `connection`.code,
    `connection`.label,
    `connection`.flow_type,
    `connection`.image,
    `connection`.client_id,
    client.random_id,
    client.secret,
    `user`.username,
    user_role.role_id,
    `group`.id as group_id
FROM akeneo_connectivity_connection `connection`
INNER JOIN pim_api_client client ON `connection`.client_id = client.id
INNER JOIN oro_user `user` ON `connection`.user_id = `user`.id
INNER JOIN oro_user_access_role user_role ON `user`.id = user_role.user_id
INNER JOIN oro_user_access_group user_group ON `user`.id = user_group.user_id
LEFT JOIN oro_access_group `group` ON user_group.group_id = `group`.id
    AND `group`.name <> :default_group
WHERE `connection`.code = :code
SQL;

        $data = $this->dbalConnection->executeQuery($selectSQL, [
            'code' => $code,
            'default_group' => User::GROUP_DEFAULT
        ])->fetchAll(FetchMode::ASSOCIATIVE);

        if (0 === count($data)) {
            return null;
        }

        // If there is more than one line, remove the one with the default user group (null).
        if (count($data) > 1) {
            $data = array_filter($data, function (array $row) {
                return null !== $row['group_id'];
            });
        }
        $row = array_pop($data);

        return new ConnectionWithCredentials(
            $row['code'],
            $row['label'],
            $row['flow_type'],
            $row['image'],
            $row['client_id'] . '_' . $row['random_id'],
            $row['secret'],
            $row['username'],
            $row['role_id'],
            $row['group_id'],
        );
    }
}
