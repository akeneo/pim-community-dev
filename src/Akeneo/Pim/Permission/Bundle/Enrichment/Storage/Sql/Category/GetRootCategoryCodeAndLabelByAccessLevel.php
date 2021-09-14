<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category;

use Akeneo\Pim\Permission\Component\Attributes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class GetRootCategoryCodeAndLabelByAccessLevel
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * This query use a subselect to compute the distinct directly on the ids of the categories.
     * This is done for performance reason.
     *
     * @param string $level must be part of 'own_items', 'edit_items' or 'view_items'
     * @param array $groupIds
     * @param string $locale
     * @param int $offset
     * @param int $limit
     *
     * @throws \LogicException if $level is not part of 'own_items', 'edit_items' or 'view_items'
     *
     * @return array<array{code: string, label: string}>
     * @return array{next: string|null, results: array{code: string, label: string}}
     */
    public function execute(string $level, array $groupIds, string $locale, int $offset = 0, int $limit = 100): array
    {
        switch ($level) {
            case Attributes::OWN:
                $permission = 'own_items';
                break;
            case Attributes::EDIT:
                $permission = 'edit_items';
                break;
            case Attributes::VIEW:
                $permission = 'view_items';
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'Level must be %s, %s or %s.',
                        Attributes::OWN,
                        Attributes::EDIT,
                        Attributes::VIEW
                    )
                );
        }

        $query = <<<SQL
SELECT category.code as code, category_label.label as label
FROM 
(
    SELECT 
        DISTINCT p1_.category_id 
    FROM 
        pimee_security_product_category_access p1_ 
    WHERE 
        p1_.%s = 1 AND
        p1_.user_group_id IN (:group_ids)
    ORDER BY p1_.category_id
) as categories
JOIN pim_catalog_category category ON category.id = categories.category_id AND category.parent_id IS NULL
JOIN pim_catalog_category_translation category_label
    ON category.id = category_label.foreign_key AND category_label.locale = :locale
LIMIT :limit OFFSET :offset;
SQL;
        $queryWithPermission = sprintf($query, $permission);

        return $this->connection->fetchAll(
            $queryWithPermission,
            [
                'group_ids' => $groupIds,
                'locale' => $locale,
                'limit' => $limit,
                'offset' => $offset,
            ],
            [
                'group_ids' => Connection::PARAM_INT_ARRAY,
                'offset' => ParameterType::INTEGER,
                'limit' => ParameterType::INTEGER,
            ]
        );
    }
}
