<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Query;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * This class has a dummy implementation to check if everything works. It is unoptimized and can lead to big latency in
 * production. The feature where this query is used is not available in production.
 * @deprecated
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetNextIdentifierQuery implements GetNextIdentifierQuery
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function fromPrefix(Target $target, string $prefix): int
    {
        $sql = <<<SQL
SELECT MAX(CAST(SUBSTRING(raw_data, :prefixLength) AS UNSIGNED))
FROM pim_catalog_product_unique_data
INNER JOIN pim_catalog_attribute ON pim_catalog_attribute.id = pim_catalog_product_unique_data.attribute_id
WHERE pim_catalog_attribute.code=:code
  AND pim_catalog_product_unique_data.raw_data LIKE :prefix;
SQL;
        $result = $this->connection->executeQuery(
            $sql,
            [
                'code' => $target->asString(),
                'prefixLength' => \strlen($prefix) + 1,
                'prefix' => $prefix . '%',
            ],
            [
                'code' => \PDO::PARAM_STR,
                'prefixLength' => \PDO::PARAM_INT,
                'prefix' => \PDO::PARAM_STR,
            ]
        )->fetchOne();

        Assert::nullOrString($result);
        if (null === $result || $result === '') {
            return 1;
        }

        return ((int) $result) + 1;
    }
}
