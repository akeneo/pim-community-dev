<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use PimEnterprise\Bundle\FilterBundle\Filter\Product\ProjectCompletenessFilter;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\TableNameMapper;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectCompletenessRepositoryInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCompletenessRepository implements ProjectCompletenessRepositoryInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var TableNameMapper */
    protected $tableNameMapper;

    /**
     * @param EntityManager   $entityManager
     * @param TableNameMapper $tableNameMapper
     */
    public function __construct(EntityManager $entityManager, TableNameMapper $tableNameMapper)
    {
        $this->entityManager = $entityManager;
        $this->tableNameMapper = $tableNameMapper;
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
    public function findProductIds(ProjectInterface $project, $status, $username)
    {
        switch ($status) {
            case ProjectCompletenessFilter::CONTRIBUTOR_TODO:
            case ProjectCompletenessFilter::CONTRIBUTOR_IN_PROGRESS:
            case ProjectCompletenessFilter::CONTRIBUTOR_DONE:
                return $this->findProductIdsAsContributor($project, $status, $username);
            case ProjectCompletenessFilter::OWNER_TODO:
            case ProjectCompletenessFilter::OWNER_IN_PROGRESS:
            case ProjectCompletenessFilter::OWNER_DONE:
                return $this->findProductIdsAsOwner($project, $status);
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
    protected function findProductIdsAsContributor(ProjectInterface $project, $status, $username)
    {
        $parameters = $this->buildQueryParameters($project, $username);

        // Filter on the categories the user can edit
        $filterByCategoryPermissionJoins = <<<FILTER_USER
LEFT JOIN `@pim_user.entity.user#groups@` AS `user_group`
ON `product_category_access`.`user_group_id` = `user_group`.`group_id`
LEFT JOIN `@pim_user.entity.user@` AS `user`
ON `user_group`.`user_id` = `user`.`id`
FILTER_USER;

        $filterByCategoryPermissionByConditions = <<<FILTER_USER
AND `user`.`username` = :username
FILTER_USER;

        // Filter on the attribute groups the user can edit
        $filterByAttributeGroupPermissions = <<<ATTRIBUTE_GROUP_FILTER
INNER JOIN `@pimee_security.entity.attribute_group_access@` AS `attribute_group_access`
    ON `completeness_per_attribute_group`.`attribute_group_id` = `attribute_group_access`.`attribute_group_id`
    AND `attribute_group_access`.`edit_attributes` = 1
INNER JOIN`@pimee_teamwork_assistant.model.project#userGroups@` AS `project_contributor_group`
    ON `project_contributor_group`.`user_group_id` = `attribute_group_access`.`user_group_id`
    AND `project_contributor_group`.`project_id` = :project_id
INNER JOIN `@pim_user.entity.user#groups@` AS `user_group`
    ON `project_contributor_group`.`user_group_id` = `user_group`.`group_id`
INNER JOIN `@pim_user.entity.user@` AS `user`
    ON `user_group`.`user_id` = `user`.`id`
WHERE `user`.`username` = :username
ATTRIBUTE_GROUP_FILTER;

        $sql = <<<SQL
SELECT `completeness_per_attribute_group`.`product_id`
FROM (
    SELECT DISTINCT `project_product`.`product_id`
    FROM `@pimee_teamwork_assistant.project_product@` AS `project_product`
    LEFT JOIN `@pim_catalog.entity.product#categories@` AS `category_product`
        ON `project_product`.`product_id` = `category_product`.`product_id`
    WHERE `project_product`.`project_id` = :project_id
    AND (
        `category_product`.`category_id` IS NULL
        OR `category_product`.`category_id` IN (
            SELECT `product_category_access`.`category_id`
            FROM `@pimee_security.entity.product_category_access@` AS `product_category_access`
            $filterByCategoryPermissionJoins
            WHERE `product_category_access`.`edit_items` = 1
            $filterByCategoryPermissionByConditions
        )
    )
) AS `product_selection`

INNER JOIN `@pimee_teamwork_assistant.completeness_per_attribute_group@` AS `completeness_per_attribute_group`
    ON `completeness_per_attribute_group`.`product_id` = `product_selection`.`product_id`
    AND `completeness_per_attribute_group`.`channel_id` = :channel_id
    AND `completeness_per_attribute_group`.`locale_id` = :locale_id
$filterByAttributeGroupPermissions
GROUP BY `completeness_per_attribute_group`.`product_id`
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
        $sql = $this->tableNameMapper->createQuery($sql);
        $productIds = $connection->fetchAll($sql, $parameters);

        return array_column($productIds, 'product_id');
    }

    /**
     * Find product ids for a given $project with the given $status, from a project overview (as owner).
     *
     * @param ProjectInterface $project
     * @param string           $status
     *
     * @return array
     */
    protected function findProductIdsAsOwner(ProjectInterface $project, $status)
    {
        $parameters = $this->buildQueryParameters($project);

        $sql = <<<SQL
SELECT `completeness_per_attribute_group`.`product_id`
FROM `@pimee_teamwork_assistant.completeness_per_attribute_group@` AS `completeness_per_attribute_group`
INNER JOIN `@pimee_teamwork_assistant.project_product@` AS `project_product`
    ON `project_product`.`product_id` = `completeness_per_attribute_group`.`product_id`
    AND `project_product`.`project_id` = :project_id
WHERE completeness_per_attribute_group.channel_id = :channel_id 
AND completeness_per_attribute_group.locale_id = :locale_id 
GROUP BY `completeness_per_attribute_group`.`product_id`
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
        $sql = $this->tableNameMapper->createQuery($sql);
        $productIds = $connection->fetchAll($sql, $parameters);

        return array_column($productIds, 'product_id');
    }

    /**
     * Build your query parameters.
     *
     * @param ProjectInterface $project
     * @param string|null      $username
     *
     * @return array
     */
    protected function buildQueryParameters(ProjectInterface $project, $username = null)
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
    protected function buildSqlQuery($username = null)
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
        FROM @pimee_teamwork_assistant.completeness_per_attribute_group@ AS completeness_attribute_group
        JOIN @pimee_teamwork_assistant.project_product@ AS project_product
            ON project_product.product_id = completeness_attribute_group.product_id
        WHERE
            completeness_attribute_group.locale_id = :locale_id
            AND completeness_attribute_group.channel_id = :channel_id
            AND project_product.project_id = :project_id
      GROUP BY completeness_attribute_group.product_id
) completeness
SQL;
        } else {
            $sql .=
<<<SQL
(
SELECT *
FROM
(
    SELECT
            completeness_attribute_group.product_id,
            SUM(completeness_attribute_group.has_at_least_one_required_attribute_filled) AS attribute_group_in_progress,
            SUM(completeness_attribute_group.is_complete) AS attribute_group_done,
            COUNT(completeness_attribute_group.product_id) AS total_attribute_group
        FROM @pimee_teamwork_assistant.completeness_per_attribute_group@ AS completeness_attribute_group

-- Attribute group access
            JOIN @pimee_security.entity.attribute_group_access@ AS attribute_group_access
                ON attribute_group_access.attribute_group_id = completeness_attribute_group.attribute_group_id
                AND attribute_group_access.edit_attributes = 1
            JOIN @pim_user.entity.user#groups@ user_group
                ON attribute_group_access.user_group_id = user_group.group_id

-- Project User Group
            JOIN @pimee_teamwork_assistant.model.project#userGroups@ project_user_group
                ON project_user_group.user_group_id = user_group.group_id
                AND project_user_group.project_id = :project_id
            JOIN @pim_user.entity.user@ AS user
                ON user.id = user_group.user_id

-- Project product selection
            JOIN @pimee_teamwork_assistant.project_product@ AS project_product
                ON project_product.project_id = :project_id
                AND project_product.product_id = completeness_attribute_group.product_id
        WHERE
            completeness_attribute_group.locale_id = :locale_id
            AND completeness_attribute_group.channel_id = :channel_id
            AND user.username = :username
      GROUP BY completeness_attribute_group.product_id
) AS completeness_processed

-- Category access filter
    WHERE EXISTS (
        SELECT *
        FROM @pimee_teamwork_assistant.project_product@ AS project_product
            LEFT JOIN @pim_catalog.entity.product#categories@ AS category_product
                ON project_product.product_id = category_product.product_id
        WHERE project_product.project_id = :project_id
            AND (
                category_product.category_id IS NULL
                OR category_product.category_id IN (
                    SELECT product_category_access.category_id
                    FROM @pimee_security.entity.product_category_access@ AS product_category_access
                        JOIN @pim_user.entity.user#groups@ user_group
                            ON user_group.group_id = product_category_access.user_group_id
                        JOIN @pim_user.entity.user@ AS user
                            ON user.id = user_group.user_id
                    WHERE product_category_access.edit_items = 1
                        AND user.username = :username
            )
        )
        AND completeness_processed.product_id = project_product.product_id
    )
) completeness
SQL;
        }

        return $this->tableNameMapper->createQuery($sql);
    }
}
