<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\Query\Sql;

use Akeneo\Channel\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetChannelCodeWithLocaleCodes implements GetChannelCodeWithLocaleCodesInterface
{
    /** @var Connection */
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function findAll(): array
    {
        $sql = <<<SQL
SELECT channel.code AS channelCode, JSON_ARRAYAGG(locale.code) AS localeCodes
FROM pim_catalog_channel channel
    LEFT JOIN pim_catalog_channel_locale channel_locale on channel.id = channel_locale.channel_id
    LEFT JOIN pim_catalog_locale locale ON channel_locale.locale_id = locale.id
GROUP BY channel.code;
SQL;

        $results = $this->connection->executeQuery($sql)->fetchAll();

        return array_map([$this, 'hydrateLocaleCodes'], $results);
    }

    private function hydrateLocaleCodes(array $row): array
    {
        $row['localeCodes'] = array_filter(\json_decode($row['localeCodes'], true));

        return $row;
    }
}
