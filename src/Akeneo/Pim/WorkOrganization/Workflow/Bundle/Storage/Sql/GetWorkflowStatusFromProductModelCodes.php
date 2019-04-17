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
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class GetWorkflowStatusFromProductModelCodes
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $userId
     * @param string[] $productModelCodes
     *
     * @return array
     */
    public function fromProductModelCodes(int $userId, array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $productModelCodes = (function (string ...$codes) {
            return $codes;
        })(... $productModelCodes);

        $query = <<<SQL
    SELECT 
        category_access_count.product_model_code,
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
            product_model_categories.id as product_model_id,
            product_model_categories.product_model_code,
            COALESCE(SUM(category_access.edit_items), 0) as count_editable_categories,
            COALESCE(SUM(category_access.own_items), 0) as count_ownable_categories,
            COALESCE(SUM(category_access.view_items), 0) as count_viewable_categories,
            COUNT(product_model_categories.category_id) as number_categories
        FROM (
            SELECT pm.id, pm.code as product_model_code, cpm.category_id
                FROM pim_catalog_product_model pm
                LEFT JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = pm.id
                WHERE pm.code IN (:productModelCodes)
            UNION
            SELECT pm.id, pm.code as product_model_code, cpm.category_id
                FROM pim_catalog_product_model pm                
                JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = pm.parent_id
                WHERE pm.code IN (:productModelCodes)            
        ) as product_model_categories
        LEFT JOIN (
            SELECT pca.category_id, pca.edit_items, pca.own_items, pca.view_items
                FROM pimee_security_product_category_access pca
                JOIN oro_access_group ag ON pca.user_group_id = ag.id
                JOIN oro_user_access_group uag ON uag.group_id = ag.id AND uag.user_id = :userId
            ) as category_access ON category_access.category_id = product_model_categories.category_id
        GROUP BY product_model_categories.product_model_code, product_model_categories.id
    ) as category_access_count
    LEFT JOIN ( 
        SELECT product_model_draft.status, product_model_draft.product_model_id
        FROM pimee_workflow_product_model_draft product_model_draft
        JOIN oro_user user ON user.username = product_model_draft.author AND user.id = :userId
    ) as draft ON category_access_count.product_model_id = draft.product_model_id
    HAVING workflow_status IS NOT NULL;
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            [
                'productModelCodes' => $productModelCodes,
                'userId' => $userId,
            ],
            [
                'productModelCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $results = [];
        foreach ($rows as $row) {
            $results[$row['product_model_code']] = $row['workflow_status'];
        }

        return $results;
    }
}
