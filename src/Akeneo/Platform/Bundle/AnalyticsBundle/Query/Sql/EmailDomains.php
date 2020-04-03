<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Query\Sql;

use Akeneo\Tool\Component\Analytics\EmailDomainsQuery;
use Doctrine\DBAL\Connection;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmailDomains implements EmailDomainsQuery
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(): string
    {
        $query = <<<SQL
            SELECT DISTINCT(SUBSTRING_INDEX(email, '@', -1)) AS email_domain
                FROM oro_user
                ORDER by email_domain
SQL;

        $domains = $this->connection->executeQuery($query)->fetchAll(\PDO::FETCH_COLUMN, 0);

        return implode(',', $domains);
    }
}
