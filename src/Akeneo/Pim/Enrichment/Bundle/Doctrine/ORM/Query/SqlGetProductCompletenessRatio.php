<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetProductCompletenessRatio implements GetProductCompletenessRatio
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forChannelCodeAndLocaleCode(int $productId, string $channelCode, string $localeCode): ?int
    {
        $sql = <<<SQL
SELECT FLOOR(((completeness.required_count - completeness.missing_count) / completeness.required_count) * 100) AS ratio
FROM pim_catalog_completeness completeness
INNER JOIN pim_catalog_channel channel on completeness.channel_id = channel.id
INNER JOIN pim_catalog_locale locale on completeness.locale_id = locale.id
WHERE completeness.product_id = :productId
AND channel.code = :channelCode
AND locale.code = :localeCode
SQL;
        $ratio = $this->connection->executeQuery(
            $sql,
            [
                'productId' => $productId,
                'channelCode' => $channelCode,
                'localeCode' => $localeCode,
            ]
        )->fetchColumn();

        return false === $ratio ? null : (int) $ratio;
    }
}
