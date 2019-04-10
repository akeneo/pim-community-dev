<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\Doctrine\Query;

use Akeneo\Channel\Component\Query\GetChannelCategoryCodeInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetChannelCategoryCode implements GetChannelCategoryCodeInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $channelCode): ?string
    {
        $sql = <<<'SQL'
            SELECT category.code
            FROM pim_catalog_channel channel
            INNER JOIN pim_catalog_category category
                ON channel.category_id = category.id
            WHERE channel.code = :channel_code
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'channel_code' => $channelCode,
            ],
            [
                'channel_code' => \PDO::PARAM_STR,
            ]
        );

        $categoryCode = $stmt->fetchColumn(0);
        if (false === $categoryCode) {
            return null;
        }

        return $categoryCode;
    }
}
