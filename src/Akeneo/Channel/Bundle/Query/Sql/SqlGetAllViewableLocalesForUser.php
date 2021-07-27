<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\Query\Sql;

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Doctrine\DBAL\Connection;

/**
 * Return all the viewable locales by a user.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetAllViewableLocalesForUser implements GetAllViewableLocalesForUserInterface
{
    private Connection $sqlConnection;

    private ?array $cache = null;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetchAll(int $userId): array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        $query = <<<SQL
            SELECT
                locale.code
            FROM
                JOIN pim_catalog_locale locale
SQL;

        $result = $this->sqlConnection
            ->executeQuery(
                $query,
                ['userId' => $userId]
            )->fetchAll(\PDO::FETCH_COLUMN);

        $this->cache = $result;

        return $result;
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
