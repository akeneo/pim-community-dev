<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetCompletenessRatio implements GetProductCompletenessRatio
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function forChannelCodeAndLocaleCode(UuidInterface $productUuid, string $channelCode, string $localeCode): ?int
    {
        $sql = <<<SQL
SELECT c.completeness
FROM pim_catalog_product_completeness c
WHERE c.product_uuid = :productUuid
SQL;
        $response = $this->connection->executeQuery(
            $sql,
            [
                'productUuid' => $productUuid->getBytes(),
            ]
        )->fetchOne();

        $completeness = json_decode($response, true);
        if (!$completeness) {
            return null;
        }

        $completenessByChannelLocale = $completeness[$channelCode][$localeCode];
        if (!$completenessByChannelLocale) {
            return null;
        }

        $missing = $completenessByChannelLocale['missing'];
        $required = $completenessByChannelLocale['required'];

        return intval(floor((($required - $missing) / $required) * 100));
    }
}
