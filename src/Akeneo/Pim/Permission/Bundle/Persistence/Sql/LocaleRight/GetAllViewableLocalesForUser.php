<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\Sql\LocaleRight;

use Akeneo\Pim\Permission\Component\Query;
use Doctrine\DBAL\Connection;

/**
 * Return all the viewable locales by a user.
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllViewableLocalesForUser implements Query\GetAllViewableLocalesForUser
{
    private Connection $sqlConnection;

    private array $cache = [];

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetchAll(int $userId): array
    {
        if (isset($this->cache[$userId])) {
            return $this->cache[$userId];
        }

        $query = <<<SQL
            SELECT
                locale.code
            FROM
                pimee_security_locale_access locale_access
                JOIN pim_catalog_locale locale ON locale.id = locale_access.locale_id
                JOIN oro_user_access_group user_access_group ON user_access_group.group_id = locale_access.user_group_id
            WHERE
                user_access_group.user_id = :userId
SQL;

        $result = $this->sqlConnection
            ->executeQuery(
                $query,
                ['userId' => $userId]
            )->fetchAll(\PDO::FETCH_COLUMN);

        $this->cache[$userId] = $result;

        return $result;
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
