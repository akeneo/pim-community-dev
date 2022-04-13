<?php


namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindChannels implements FindChannels, CachedQueryInterface
{
    private ?array $cache = null;

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @inerhitDoc
     */
    public function findAll(): array
    {
        if (null === $this->cache) {
            $sql = <<<SQL
                SELECT 
                    c.code AS channelCode, 
                    JSON_OBJECTAGG(l.id, l.code) AS localeCodes,
                    JSON_OBJECTAGG(ct.locale, ct.label) AS labels,
                    JSON_OBJECTAGG(cur.id, cur.code) AS activatedCurrencies
                FROM pim_catalog_channel c
                LEFT JOIN pim_catalog_channel_locale cl 
                    ON c.id = cl.channel_id
                LEFT JOIN pim_catalog_locale l 
                    ON cl.locale_id = l.id
                LEFT JOIN pim_catalog_channel_translation ct 
                    ON c.id = ct.foreign_key
                LEFT JOIN pim_catalog_channel_currency cc 
                    ON c.id = cc.channel_id
                LEFT JOIN pim_catalog_currency cur 
                    ON cc.currency_id = cur.id
                GROUP BY c.code;
            SQL;

            $results = $this->connection->executeQuery($sql)->fetchAllAssociative();
            $channels = [];

            foreach ($results as $result) {
                $channels[] = new Channel(
                    $result['channelCode'],
                    array_values(json_decode($result['localeCodes'], true)),
                    LabelCollection::fromArray(json_decode($result['labels'], true)),
                    array_values(json_decode($result['activatedCurrencies'], true))
                );
            }

            $this->cache = $channels;
        }

        return $this->cache;
    }

    public function clearCache(): void
    {
        $this->cache = null;
    }
}
