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

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;

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
  INNER JOIN akeneo_pim.pim_catalog_channel_locale AS channel_locale ON (channel.id = channel_locale.channel_id)
  INNER JOIN akeneo_pim.pim_catalog_locale AS locale ON (channel_locale.locale_id = locale.id)
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
