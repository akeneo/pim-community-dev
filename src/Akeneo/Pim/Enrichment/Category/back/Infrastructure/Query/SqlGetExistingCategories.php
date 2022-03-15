<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * In CE, viewable categories are the existing ones.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetExistingCategories implements GetViewableCategories
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function forUserId(array $categoryCodes, int $userId): array
    {
        if ([] === $categoryCodes) {
            return [];
        }

        Assert::allString($categoryCodes);
        $query = 'SELECT code from pim_catalog_category WHERE code IN (:codes)';

        return $this->connection->executeQuery(
            $query,
            ['codes' => $categoryCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();
    }
}
