<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * Return all the viewable locales by a user.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @deprecated Use Akeneo\Channel\Infrastructure\Query\Sql\SqlFindAllViewableLocalesForUser
 */
class SqlGetAllViewableLocalesForUser implements GetAllViewableLocalesForUserInterface, CachedQueryInterface
{
    private Connection $sqlConnection;

    private ?array $cache = null;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetchAll(int $userId): array
    {
        if (null === $this->cache) {
            $query = <<<SQL
                SELECT locale.code
                FROM pim_catalog_locale locale
            SQL;

            $this->cache = $this->sqlConnection->executeQuery($query)->fetchFirstColumn();
        }

        return $this->cache;
    }

    public function clearCache(): void
    {
        $this->cache = null;
    }
}
