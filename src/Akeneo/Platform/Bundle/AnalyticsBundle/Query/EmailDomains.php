<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Query;

use Doctrine\DBAL\Connection;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmailDomains
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(): array
    {
        $results = [];

        $sql = <<<SQL
            SELECT COUNT(id) as user_count, SUBSTRING_INDEX(email, '@', -1) AS email_domain
                FROM oro_user
                GROUP BY email_domain;
SQL;

        $rows = $this->connection->fetchAll($sql);

        foreach ($rows as $row) {
            $results[$row['email_domain']] = $row['user_count'];
        }

        return $results;
    }
}
