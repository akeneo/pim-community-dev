<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindActivatedLocalesPerChannels
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * Returns a list of channels with associated activated locales:
     * [
     *      'ecommerce' => ['fr_FR', 'en_US']
     *      'mobile' => ['en_US']
     * ]
     */
    public function __invoke(): array
    {
        $query = <<<SQL
SELECT c.code as channel_code, JSON_ARRAYAGG(l.code) AS locales_codes
FROM pim_catalog_channel c INNER JOIN pim_catalog_channel_locale cl on c.id = cl.channel_id 
INNER JOIN pim_catalog_locale l ON cl.locale_id = l.id
WHERE l.is_activated = 1
GROUP BY c.code;
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
