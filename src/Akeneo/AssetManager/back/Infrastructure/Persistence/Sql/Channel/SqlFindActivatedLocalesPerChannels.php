<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Channel;

use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindActivatedLocalesPerChannels implements FindActivatedLocalesPerChannelsInterface
{
    private Connection $sqlConnection;

    // TODO caching ?

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $query = <<<SQL
SELECT c.code as channel_code, JSON_ARRAYAGG(l.code) AS locales_codes
FROM pim_catalog_channel c INNER JOIN pim_catalog_channel_locale cl on c.id = cl.channel_id 
INNER JOIN pim_catalog_locale l ON cl.locale_id = l.id
WHERE l.is_activated = 1
GROUP BY c.code
SQL;
        $statement = $this->sqlConnection->executeQuery($query);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $matrix = [];
        foreach ($results as $result) {
            $matrix[$result['channel_code']] = json_decode($result['locales_codes']);
        }

        return $matrix;
    }
}
