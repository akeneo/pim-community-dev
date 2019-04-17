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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Doctrine\DBAL\Connection;

/**
 * A query to get workflow status given a list of product identifiers
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class GetWorkflowStatusFromProductIdentifiers
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * @param int $userId
     * @param string[] $productIdentifiers
     *
     * @return array
     */
    public function fromProductIdentifiers(int $userId, array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $query = <<<SQL
    SELECT 
        category_access_count.product_identifier,
        CASE
            WHEN category_access_count.count_ownable_categories > 0 OR category_access_count.number_categories = 0 THEN 'working_copy'
            WHEN category_access_count.count_editable_categories > 0 AND draft.status IS NULL THEN 'working_copy'
            WHEN category_access_count.count_editable_categories > 0 AND draft.status = 1 THEN 'proposal_waiting_for_approval'
            WHEN category_access_count.count_editable_categories > 0 AND draft.status = 0 THEN 'draft_in_progress'
            WHEN category_access_count.count_viewable_categories > 0 THEN 'read_only'
            ELSE NULL
        END as workflow_status
    FROM (
        SELECT
            product_categories.id as product_id,
            product_categories.product_identifier,
            COALESCE(SUM(category_access.edit_items), 0) as count_editable_categories,
            COALESCE(SUM(category_access.own_items), 0) as count_ownable_categories,
            COALESCE(SUM(category_access.view_items), 0) as count_viewable_categories,
            COUNT(product_categories.category_id) as number_categories
        FROM (
            SELECT p.id, p.identifier as product_identifier, cp.category_id
                FROM pim_catalog_product p
                LEFT JOIN pim_catalog_category_product cp ON cp.product_id = p.id
                WHERE p.identifier IN (:productIdentifiers)
            UNION
            SELECT p.id, p.identifier as product_identifier, cp.category_id
                FROM pim_catalog_product p
                JOIN pim_catalog_product_model pm ON pm.id = p.product_model_id
                JOIN pim_catalog_category_product_model cp ON cp.product_model_id = pm.id
                WHERE p.identifier IN (:productIdentifiers)
            UNION
            SELECT p.id, p.identifier as product_identifier, cp.category_id
                FROM pim_catalog_product p
                JOIN pim_catalog_product_model pm1 ON pm1.id = p.product_model_id
                JOIN pim_catalog_product_model pm2 on pm2.id = pm1.parent_id
                JOIN pim_catalog_category_product_model cp ON cp.product_model_id = pm2.id
                WHERE p.identifier IN (:productIdentifiers)
        ) as product_categories
        LEFT JOIN (
            SELECT pca.category_id, pca.edit_items, pca.own_items, pca.view_items
                FROM pimee_security_product_category_access pca
                JOIN oro_access_group ag ON pca.user_group_id = ag.id
                JOIN oro_user_access_group uag ON uag.group_id = ag.id AND uag.user_id = :userId
            ) as category_access ON category_access.category_id = product_categories.category_id
        GROUP BY product_categories.product_identifier, product_categories.id
    ) as category_access_count
    LEFT JOIN (
        SELECT product_draft.status, product_draft.product_id
        FROM pimee_workflow_product_draft product_draft
        JOIN oro_user user ON user.username = product_draft.author AND user.id = :userId
    ) as draft ON category_access_count.product_id = draft.product_id
    HAVING workflow_status IS NOT NULL;
SQL;

        $rows = $this->sqlConnection->fetchAll($query, [
            'userId' => $userId,
            'productIdentifiers' => $productIdentifiers
        ],
        [
            'productIdentifiers' => Connection::PARAM_STR_ARRAY
        ]);

        $results = [];

        foreach ($rows as $row) {
            $results[$row['product_identifier']] = $row['workflow_status'];
        }

        return $results;
    }
}
