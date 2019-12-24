<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Apps\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Apps\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQuery;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionWithCredentialsByCodeQuery implements SelectConnectionWithCredentialsByCodeQuery
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
SELECT app.code, app.label, app.flow_type, app.image, app.client_id, client.random_id, client.secret, u.username
FROM akeneo_app app 
INNER JOIN pim_api_client client on app.client_id = client.id
INNER JOIN oro_user u on app.user_id = u.id
WHERE code = :code
SQL;
        $dataRow = $this->dbalConnection->executeQuery($selectSQL, ['code' => $code])->fetch();
        if (!$dataRow) {
            return null;
        }

        return new ConnectionWithCredentials(
            $dataRow['code'],
            $dataRow['label'],
            $dataRow['flow_type'],
            $dataRow['client_id'] .'_'. $dataRow['random_id'],
            $dataRow['secret'],
            $dataRow['username'],
            null,
            $dataRow['image'],
        );
    }
}
