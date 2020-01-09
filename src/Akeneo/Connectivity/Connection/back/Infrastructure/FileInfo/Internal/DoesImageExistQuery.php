<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\FileInfo\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DoesImageExistQueryInterface;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoesImageExistQuery implements DoesImageExistQueryInterface
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $filePath): bool
    {
        $selectSQL = <<<SQL
SELECT count(1) as count
FROM akeneo_file_storage_file_info
WHERE file_key = :filePath
SQL;

        $count = $this->dbalConnection->executeQuery($selectSQL, ['filePath' => $filePath])->fetch()['count'];

        return boolval($count);
    }
}
