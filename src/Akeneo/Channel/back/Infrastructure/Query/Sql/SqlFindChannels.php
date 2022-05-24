<?php


namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindChannels implements FindChannels
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function findByCodes(array $codes): array
    {
        $sql = <<<SQL
            SELECT 
                channel.code AS channelCode, 
                JSON_REMOVE(JSON_OBJECTAGG(IFNULL(locale.id, 'NO_LOCALE'), locale.code), '$.NO_LOCALE') AS localeCodes,
                JSON_REMOVE(JSON_OBJECTAGG(IFNULL(channel_translation.locale, 'NO_LABEL'), channel_translation.label), '$.NO_LABEL') AS labels,
                JSON_REMOVE(JSON_OBJECTAGG(IFNULL(currency.id, 'NO_CURRENCY'), currency.code), '$.NO_CURRENCY') AS activatedCurrencies
            FROM pim_catalog_channel channel
            LEFT JOIN pim_catalog_channel_locale channel_locale 
                ON channel.id = channel_locale.channel_id
            LEFT JOIN pim_catalog_locale locale
                ON channel_locale.locale_id = locale.id
            LEFT JOIN pim_catalog_channel_translation channel_translation 
                ON channel.id = channel_translation.foreign_key
            LEFT JOIN pim_catalog_channel_currency channel_currency 
                ON channel.id = channel_currency.channel_id
            LEFT JOIN pim_catalog_currency currency 
                ON channel_currency.currency_id = currency.id
            WHERE channel.code IN (:channel_codes)
            GROUP BY channel.code;
        SQL;

        $results = $this->connection->executeQuery(
            $sql,
            ['channel_codes' => $codes],
            ['channel_codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();
        $channels = [];

        foreach ($results as $result) {
            $channels[] = new Channel(
                $result['channelCode'],
                array_values(json_decode($result['localeCodes'], true)),
                LabelCollection::fromArray(json_decode($result['labels'], true)),
                array_values(json_decode($result['activatedCurrencies'], true))
            );
        }

        return $channels;
    }

    /**
     * @return Channel[]
     */
    public function findAll(): array
    {
        $sql = <<<SQL
            SELECT 
                channel.code AS channelCode, 
                JSON_REMOVE(JSON_OBJECTAGG(IFNULL(locale.id, 'NO_LOCALE'), locale.code), '$.NO_LOCALE') AS localeCodes,
                JSON_REMOVE(JSON_OBJECTAGG(IFNULL(channel_translation.locale, 'NO_LABEL'), channel_translation.label), '$.NO_LABEL') AS labels,
                JSON_REMOVE(JSON_OBJECTAGG(IFNULL(currency.id, 'NO_CURRENCY'), currency.code), '$.NO_CURRENCY') AS activatedCurrencies
            FROM pim_catalog_channel channel
            LEFT JOIN pim_catalog_channel_locale channel_locale 
                ON channel.id = channel_locale.channel_id
            LEFT JOIN pim_catalog_locale locale
                ON channel_locale.locale_id = locale.id
            LEFT JOIN pim_catalog_channel_translation channel_translation 
                ON channel.id = channel_translation.foreign_key
            LEFT JOIN pim_catalog_channel_currency channel_currency 
                ON channel.id = channel_currency.channel_id
            LEFT JOIN pim_catalog_currency currency 
                ON channel_currency.currency_id = currency.id
            GROUP BY channel.code;
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

        return $channels;
    }
}
