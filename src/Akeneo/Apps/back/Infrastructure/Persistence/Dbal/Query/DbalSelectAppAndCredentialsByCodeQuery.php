<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Apps\Domain\Model\Read\AppAndCredentials;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppAndCredentialsByCodeQuery;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectAppAndCredentialsByCodeQuery implements SelectAppAndCredentialsByCodeQuery
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $code): ?AppAndCredentials
    {
        $selectSQL = <<<SQL
SELECT app.code, app.label, app.flow_type, client.random_id, client.secret
FROM akeneo_app app 
INNER JOIN pim_api_client client on app.client_id = client.id
WHERE code = :code
SQL;
        $dataRow = $this->dbalConnection->executeQuery($selectSQL, ['code' => $code])->fetch();

        return new AppAndCredentials(
            $dataRow['code'],
            $dataRow['label'],
            $dataRow['flow_type'],
            $dataRow['client_id'],
            $dataRow['secret']
        );
    }
}
