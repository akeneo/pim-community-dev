<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Query\Sql\Channel;

use Akeneo\Pim\Automation\RuleEngine\Component\Query\GetChannelCodeWithLocaleCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
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

        return array_map([$this, 'convertJsonColumn'], $results);
    }

    private function convertJsonColumn(array $row): array
    {
        $row['localeCodes'] = array_filter(\json_decode($row['localeCodes'], true));

        return $row;
    }
}
