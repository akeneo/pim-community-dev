<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\Filter\ProjectCompletenessFilter;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\TableNameMapper;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectCompletenessRepositoryInterface;
use Doctrine\ORM\EntityManager;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCompletenessRepository implements ProjectCompletenessRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @param EntityManager   $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectCompleteness(ProjectInterface $project, $username = null)
    {
        $query = $this->buildSqlQuery($username);
        $parameters = $this->buildQueryParameters($project, $username);
        $completeness = $this->entityManager->getConnection()->fetchAssoc($query, $parameters);

        return new ProjectCompleteness($completeness['todo'], $completeness['in_progress'], $completeness['done']);
    }

    /**
     * {@inheritdoc}
     */
    public function findProductIdentifiers(ProjectInterface $project, $status, $username)
    {
        switch ($status) {
            case ProjectCompletenessFilter::CONTRIBUTOR_TODO:
            case ProjectCompletenessFilter::CONTRIBUTOR_IN_PROGRESS:
            case ProjectCompletenessFilter::CONTRIBUTOR_DONE:
                return $this->findProductIdentifiersAsContributor($project, $status, $username);
            case ProjectCompletenessFilter::OWNER_TODO:
            case ProjectCompletenessFilter::OWNER_IN_PROGRESS:
            case ProjectCompletenessFilter::OWNER_DONE:
                return $this->findProductIdentifiersAsOwner($project, $status);
            default:
                return [];
        }
    }

    /**
     * Find product ids for a given $project with the given $status, only for the $username contributor.
     *
     * @param ProjectInterface $project
     * @param string           $status
     * @param string           $username
     *
     * @return array
     */
    private function findProductIdentifiersAsContributor(ProjectInterface $project, $status, $username)
    {
        $parameters = $this->buildQueryParameters($project, $username);

        // Filter on the categories the user can edit
        $filterByCategoryPermissionJoins = <<<FILTER_USER
LEFT JOIN `oro_user_access_group` AS `user_group`
ON `product_category_access`.`user_group_id` = `user_group`.`group_id`
LEFT JOIN `oro_user` AS `user`
ON `user_group`.`user_id` = `user`.`id`
FILTER_USER;

        $filterByCategoryPermissionByConditions = <<<FILTER_USER
AND `user`.`username` = :username
FILTER_USER;

        // Filter on the attribute groups the user can edit
        $filterByAttributeGroupPermissions = <<<ATTRIBUTE_GROUP_FILTER
INNER JOIN `pimee_security_attribute_group_access` AS `attribute_group_access`
    ON `completeness_per_attribute_group`.`attribute_group_id` = `attribute_group_access`.`attribute_group_id`
    AND `attribute_group_access`.`edit_attributes` = 1
INNER JOIN`pimee_teamwork_assistant_project_user_group` AS `project_contributor_group`
    ON `project_contributor_group`.`user_group_id` = `attribute_group_access`.`user_group_id`
    AND `project_contributor_group`.`project_id` = :project_id
INNER JOIN `oro_user_access_group` AS `user_group`
    ON `project_contributor_group`.`user_group_id` = `user_group`.`group_id`
INNER JOIN `oro_user` AS `user`
    ON `user_group`.`user_id` = `user`.`id`
WHERE `user`.`username` = :username
ATTRIBUTE_GROUP_FILTER;

        $sql = <<<SQL
SELECT `product`.`identifier`
FROM (
    SELECT DISTINCT `project_product`.`product_id`
    FROM `pimee_teamwork_assistant_project_product` AS `project_product`
    LEFT JOIN `pim_catalog_category_product` AS `category_product`
        ON `project_product`.`product_id` = `category_product`.`product_id`
    WHERE `project_product`.`project_id` = :project_id
    AND (
        `category_product`.`category_id` IS NULL
        OR `category_product`.`category_id` IN (
            SELECT `product_category_access`.`category_id`
            FROM `pimee_security_product_category_access` AS `product_category_access`
            $filterByCategoryPermissionJoins
            WHERE `product_category_access`.`edit_items` = 1
            $filterByCategoryPermissionByConditions
        )
    )
) AS `product_selection`

INNER JOIN `pim_catalog_product` AS `product`
    ON `product`.`id` = `product_selection`.`product_id`

INNER JOIN `pimee_teamwork_assistant_completeness_per_attribute_group` AS `completeness_per_attribute_group`
    ON `completeness_per_attribute_group`.`product_id` = `product_selection`.`product_id`
    AND `completeness_per_attribute_group`.`channel_id` = :channel_id
    AND `completeness_per_attribute_group`.`locale_id` = :locale_id
$filterByAttributeGroupPermissions
GROUP BY `product`.`identifier`
SQL;

        if ($status === ProjectCompletenessFilter::CONTRIBUTOR_TODO) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) = 0 AND SUM(`completeness_per_attribute_group`.`has_at_least_one_required_attribute_filled`) = 0)
SQL;
        }

        // IN PROGRESS
        if ($status === ProjectCompletenessFilter::CONTRIBUTOR_IN_PROGRESS) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) > 0 OR SUM(`completeness_per_attribute_group`.`has_at_least_one_required_attribute_filled`) > 0)
AND SUM(`completeness_per_attribute_group`.`is_complete`) <> COUNT(`completeness_per_attribute_group`.`product_id`)
SQL;
        }

        // DONE
        if ($status === ProjectCompletenessFilter::CONTRIBUTOR_DONE) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) = COUNT(`completeness_per_attribute_group`.`product_id`))
SQL;
        }

        $connection = $this->entityManager->getConnection();
        $productIdentifiers = $connection->fetchAll($sql, $parameters);

        return array_column($productIdentifiers, 'identifier');
    }

    /**
     * Find product ids for a given $project with the given $status, from a project overview (as owner).
     *
     * @param ProjectInterface $project
     * @param string           $status
     *
     * @return array
     */
    private function findProductIdentifiersAsOwner(ProjectInterface $project, $status)
    {
        $parameters = $this->buildQueryParameters($project);

        $sql = <<<SQL
SELECT `product`.`identifier`
FROM 
     `pim_catalog_product` AS `product`
      INNER JOIN `pimee_teamwork_assistant_completeness_per_attribute_group` AS `completeness_per_attribute_group`
        ON `product`.`id` = `completeness_per_attribute_group`.`product_id` 
        AND completeness_per_attribute_group.channel_id = :channel_id 
        AND completeness_per_attribute_group.locale_id = :locale_id 
      INNER JOIN `pimee_teamwork_assistant_project_product` AS `project_product`
        ON `project_product`.`product_id` = `completeness_per_attribute_group`.`product_id`
        AND `project_product`.`project_id` = :project_id
GROUP BY `product`.`identifier`
SQL;

        if ($status === ProjectCompletenessFilter::OWNER_TODO) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) = 0 AND SUM(`completeness_per_attribute_group`.`has_at_least_one_required_attribute_filled`) = 0)
SQL;
        }

        // IN PROGRESS
        if ($status === ProjectCompletenessFilter::OWNER_IN_PROGRESS) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) > 0 OR SUM(`completeness_per_attribute_group`.`has_at_least_one_required_attribute_filled`) > 0)
AND SUM(`completeness_per_attribute_group`.`is_complete`) <> COUNT(`completeness_per_attribute_group`.`product_id`)
SQL;
        }

        // DONE
        if ($status === ProjectCompletenessFilter::OWNER_DONE) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) = COUNT(`completeness_per_attribute_group`.`product_id`))
SQL;
        }

        $connection = $this->entityManager->getConnection();
        $productIdentifiers = $connection->fetchAll($sql, $parameters);

        return array_column($productIdentifiers, 'identifier');
    }

    /**
     * Build your query parameters.
     *
     * @param ProjectInterface $project
     * @param string|null      $username
     *
     * @return array
     */
    private function buildQueryParameters(ProjectInterface $project, $username = null)
    {
        $parameters = [
            'project_id' => $project->getId(),
            'channel_id' => $project->getChannel()->getId(),
            'locale_id'  => $project->getLocale()->getId(),
        ];

        if (null !== $username) {
            $parameters['username'] = $username;
        }

        return $parameters;
    }

    /**
     * Build the project completeness query.
     *
     * @param string|null $username
     *
     * @return string
     */
    private function buildSqlQuery($username = null)
    {
        $sql =
            <<<SQL
SELECT
    COALESCE(
        SUM(
            CASE
                WHEN attribute_group_in_progress = 0 AND attribute_group_done = 0
                THEN 1 ELSE 0
            END
        ),
        0
    ) AS todo,
    COALESCE(
        SUM(
            CASE
                WHEN attribute_group_done <> total_attribute_group AND (attribute_group_in_progress > 0 OR attribute_group_done > 0)
                THEN 1 ELSE 0
            END
        ),
        0
    ) AS in_progress,
    COALESCE(
        SUM(
            CASE
                WHEN attribute_group_done = total_attribute_group
                THEN 1 ELSE 0
            END
        ),
        0
    ) AS done
FROM
SQL;

        if (null === $username) {
            $sql .=
                <<<SQL
(
    SELECT
            SUM(completeness_attribute_group.has_at_least_one_required_attribute_filled) AS attribute_group_in_progress,
            SUM(completeness_attribute_group.is_complete) AS attribute_group_done,
            COUNT(completeness_attribute_group.product_id) AS total_attribute_group
        FROM pimee_teamwork_assistant_completeness_per_attribute_group AS completeness_attribute_group
        JOIN pimee_teamwork_assistant_project_product AS project_product
            ON project_product.product_id = completeness_attribute_group.product_id
        WHERE
            completeness_attribute_group.locale_id = :locale_id
            AND completeness_attribute_group.channel_id = :channel_id
            AND project_product.project_id = :project_id
      GROUP BY completeness_attribute_group.product_id
) completeness
SQL;
        } else {
            /*
             * This query:
             * - get only the editable products in the TWA project for the given user
             * - get all editable attribute group type for the given user
             * - and then, combine the two: it  only selects the completeness of the editable attribute group of the editable products
             * then, it aggregates the result to know the number of products
             *
             * This query can generate a huge result set to aggregate, and can't be optimized much more without table denormalization.
             * For example, 20k editable products x 10 editable attribute group type = 200k lines to aggregate
             */
            $sql =
                <<<SQL
WITH user AS (
    SELECT id
    FROM oro_user
    WHERE oro_user.username = :username
),
     project_product AS (
         SELECT product_id
         FROM pimee_teamwork_assistant_project_product AS project_product
         WHERE project_product.project_id = :project_id
     ),
     product_without_category AS (
         SELECT project_product.product_id
         FROM project_product
                  LEFT JOIN pim_catalog_category_product AS category_product
                            ON project_product.product_id = category_product.product_id
         WHERE category_product.category_id IS NULL
     ),
     product_with_at_least_one_editable_category AS (
         SELECT product_id
         FROM project_product
         WHERE EXISTS(
                       SELECT *
                       FROM pim_catalog_category_product AS category_product
                                JOIN pimee_security_product_category_access category_access
                                     ON category_product.category_id = category_access.category_id
                                JOIN oro_user_access_group group_access
                                     ON group_access.group_id = category_access.user_group_id
                                JOIN user ON user.id = group_access.user_id
                       WHERE category_product.product_id = project_product.product_id
                         AND category_access.edit_items = 1
                   )
     ),
     editable_product AS (
         SELECT product_id
         FROM product_with_at_least_one_editable_category
         UNION ALL
         SELECT product_id
         FROM product_without_category
     ),
     completeness_attribute_group AS (
         SELECT completeness_attribute_group.product_id,
                completeness_attribute_group.has_at_least_one_required_attribute_filled,
                completeness_attribute_group.is_complete,
                completeness_attribute_group.attribute_group_id
         FROM pimee_teamwork_assistant_completeness_per_attribute_group completeness_attribute_group
                  JOIN editable_product ON editable_product.product_id = completeness_attribute_group.product_id
         WHERE completeness_attribute_group.locale_id = :locale_id
           AND completeness_attribute_group.channel_id = :channel_id
     ),
     user_attribute_group AS (
         SELECT group_id AS user_group_id
         FROM user
                  JOIN oro_user_access_group user_group
                       ON user_group.user_id = user.id
                  JOIN pimee_teamwork_assistant_project_user_group project_user_group
                       ON project_user_group.user_group_id = user_group.group_id
                           AND project_user_group.project_id = :project_id
     ),
     user_editable_attribute_group AS (
         SELECT DISTINCT attribute_group_id
         FROM pimee_security_attribute_group_access AS attribute_group_access
                  JOIN user_attribute_group
                       ON user_attribute_group.user_group_id = attribute_group_access.user_group_id
         WHERE attribute_group_access.edit_attributes = 1
     ),
     editable_completeness_attribute_group AS (
         SELECT completeness_attribute_group.*
         FROM user_editable_attribute_group
                  JOIN completeness_attribute_group
                       ON user_editable_attribute_group.attribute_group_id =
                          completeness_attribute_group.attribute_group_id
     ),
     progress_per_product AS (
         SELECT product_id,
                SUM(has_at_least_one_required_attribute_filled) AS attribute_group_in_progress,
                SUM(is_complete)                                AS attribute_group_done,
                COUNT(*)                                        AS total_attribute_group
         FROM editable_completeness_attribute_group
         GROUP BY product_id
     )
SELECT COALESCE(
               SUM(
                       CASE
                           WHEN attribute_group_in_progress = 0 AND attribute_group_done = 0
                               THEN 1
                           ELSE 0
                           END
                   ),
               0
           ) AS todo,
       COALESCE(
               SUM(
                       CASE
                           WHEN attribute_group_done <> total_attribute_group AND
                                (attribute_group_in_progress > 0 OR attribute_group_done > 0)
                               THEN 1
                           ELSE 0
                           END
                   ),
               0
           ) AS in_progress,
       COALESCE(
               SUM(
                       CASE
                           WHEN attribute_group_done = total_attribute_group
                               THEN 1
                           ELSE 0
                           END
                   ),
               0
           ) AS done
FROM progress_per_product
SQL;
        }

        return $sql;
    }
}
