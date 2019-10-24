<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\Doctrine\Query;

use Doctrine\DBAL\Connection;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetChannelActiveLocaleCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $channelCode): array
    {
        $sql = <<<SQL
SELECT DISTINCT locale.code
FROM pim_catalog_channel AS channel
  INNER JOIN pim_catalog_channel_locale AS channel_locale ON (channel.id = channel_locale.channel_id)
  INNER JOIN pim_catalog_locale AS locale ON (channel_locale.locale_id = locale.id)
WHERE channel.code = :channel_code
SQL;
        $statement = $this->connection->executeQuery(
            $sql,
            ['channel_code' => $channelCode],
            ['channel_code' => \PDO::PARAM_STR]
        );

        return array_map(function ($value) {
            return $value['code'];
        }, $statement->fetchAll());
    }
}
