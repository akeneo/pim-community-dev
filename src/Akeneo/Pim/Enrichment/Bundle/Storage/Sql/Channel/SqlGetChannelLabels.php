<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Channel;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use Doctrine\DBAL\Connection;

/**
 * Executes SQL query to get the stored labels of a collection of channels.
 *
 * Returns an array like:
 * [
 *      'print' => [
 *          'en_US' => 'Print',
 *          'fr_FR' => 'Impression',
 *          'de_DE' => 'Drucken'
 *      ], ...
 * ]
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetChannelLabels implements GetChannelLabelsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forChannelCodes(array $channelCodes): array
    {
        $sql = <<<SQL
SELECT
   channel.code AS code,
   trans.label AS label,
   trans.locale AS locale
FROM pim_catalog_channel channel
INNER JOIN pim_catalog_channel_translation trans ON channel.id=trans.foreign_key
WHERE channel.code IN (:channelCodes)
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['channelCodes' => $channelCodes],
            ['channelCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']][$row['locale']] = $row['label'];
        }

        return $result;
    }
}
