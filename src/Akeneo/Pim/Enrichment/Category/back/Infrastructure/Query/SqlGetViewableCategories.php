<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoEnterprise\Pim\Enrichment\Category\Infrastructure\Query;

use Akeneo\Category\Domain\Query\GetViewableCategories;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

final class SqlGetViewableCategories implements GetViewableCategories
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function forUserId(array $categoryCodes, int $userId): array
    {
        if ([] === $categoryCodes) {
            return [];
        }

        Assert::allString($categoryCodes);

        $query = <<<SQL
        SELECT DISTINCT c.code
        FROM pim_catalog_category c
            INNER JOIN pimee_security_product_category_access pca ON pca.category_id = c.id
            INNER JOIN oro_user_access_group ug ON ug.group_id = pca.user_group_id
        WHERE c.code IN (:category_codes) AND ug.user_id = :user_id
            AND pca.view_items = 1
        SQL;

        return $this->connection->executeQuery(
            $query,
            ['user_id' => $userId, 'category_codes' => $categoryCodes],
            ['category_codes' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();
    }
}
