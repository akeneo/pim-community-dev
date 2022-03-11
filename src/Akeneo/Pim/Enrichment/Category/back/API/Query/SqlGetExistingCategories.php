<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\API\Query;

use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetExistingCategories implements GetExistingCategories
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function forCodes(array $categoryCodes): array
    {
        if ([] === $categoryCodes) {
            return [];
        }

        Assert::allString($categoryCodes);

        $query = <<<SQL
SELECT code from pim_catalog_category WHERE code IN (:codes);
SQL;


        return $this->connection->executeQuery(
            $query,
            ['codes' => $categoryCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();
    }
}
