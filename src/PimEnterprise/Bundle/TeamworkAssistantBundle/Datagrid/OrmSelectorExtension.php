<?php
/**
 * Created by PhpStorm.
 * User: arnaud
 * Date: 27/02/17
 * Time: 17:00
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Datagrid;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\From;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Bundle\DataGridBundle\Extension\Selector\OrmSelectorExtension as BaseOrmSelectorExtensionuse;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface as OroDatasourceInterface;

class OrmSelectorExtension extends BaseOrmSelectorExtensionuse
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ProjectRepositoryInterface */
    private $projectRepository;

    public function __construct(
        $storageDriver,
        RequestParameters $requestParams = null,
        TokenStorageInterface $tokenStorage,
        ProjectRepositoryInterface $projectRepository
    ) {
        parent::__construct($storageDriver, $requestParams);

        $this->projectRepository = $projectRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, OroDatasourceInterface $datasource)
    {
        $entityIds = $this->getEntityIds($datasource);
        $rootAlias = $datasource->getQueryBuilder()->getRootAlias();
        $rootField = $rootAlias.'.id';

        if (is_array($entityIds)) {
            $datasource->getQueryBuilder()
                ->andWhere($rootField.' IN (:entityIds)')->setParameter('entityIds', $entityIds);

            $datasource->getQueryBuilder()->setFirstResult(null)->setMaxResults(null);
        }

        $selectors = $this->getSelectorsToApply($config);
        foreach ($selectors as $selector) {
            $selector->apply($datasource, $config);
        }
    }

    /**
     * Retrieve entity ids, filters, sorters and limits are already in the datasource query builder
     *
     * @param DatasourceInterface $datasource
     *
     * @return array
     */
    protected function getEntityIds(DatasourceInterface $datasource)
    {
        $getIdsQb = clone $datasource->getQueryBuilder();
        $rootEntity = current($getIdsQb->getRootEntities());
        $rootAlias = $getIdsQb->getRootAlias();
        $rootField = $rootAlias.'.id';
        $getIdsQb->add('from', new From($rootEntity, $rootAlias, $rootField), false);
        $getIdsQb->groupBy($rootField);
        QueryBuilderUtility::removeExtraParameters($getIdsQb);

        $parameters = $this->requestParams->getRootParameterValue();
        $filter = $viewId = null;
        if (isset($parameters['_parameters']['view']['id']) && !empty($parameters['_parameters']['view']['id'])) {
            $viewId = $parameters['_parameters']['view']['id'];
        }

        if (isset($parameters['_filter']['adrien']['value'])) {
            $filter = (int)$parameters['_filter']['adrien']['value'];
        }

        if (null !== $this->projectRepository->findOneBy(['datagridView' => $parameters['_parameters']['view']['id']]) &&
            null !== $filter
        ) {
            $productIds = $this->getProjectProducts($getIdsQb, $viewId, $filter);
            if (0 === count($productIds)) {
                return [];
            }

            $alias = $getIdsQb->getRootAliases()[0];
            $getIdsQb->andWhere($getIdsQb->expr()->in($alias.'.id', $productIds));
        }

        $results = $getIdsQb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        return array_keys($results);
    }

    protected function getProjectProducts($queryBuilder, $viewId, $filter)
    {
        $sql = <<<SQL
SELECT `completeness_per_attribute_group`.`product_id`
FROM `pimee_activity_manager_project` AS `project`
INNER JOIN `pimee_activity_manager_project_product` AS `project_product`
	ON  `project_product`.`project_id` = `project`.`id`
INNER JOIN `pimee_activity_manager_completeness_per_attribute_group` AS `completeness_per_attribute_group`
	ON `project_product`.`product_id` = `completeness_per_attribute_group`.`product_id`
    AND `project`.`channel_id` = `completeness_per_attribute_group`.`channel_id`
    AND `project`.`locale_id` = `completeness_per_attribute_group`.`locale_id`
INNER JOIN `pimee_security_attribute_group_access` AS `attribute_group_access`
    ON `completeness_per_attribute_group`.`attribute_group_id` = `attribute_group_access`.`attribute_group_id`
    AND `attribute_group_access`.`edit_attributes` = 1
INNER JOIN `oro_user_access_group` AS `user_group`
    ON `attribute_group_access`.`user_group_id` = `user_group`.`group_id`
INNER JOIN `oro_user` AS `user`
    ON `user_group`.`user_id` = `user`.`id`
WHERE `project`.`datagrid_view_id` = :datagrid_view_id
AND `user`.`username` = :username
GROUP BY `completeness_per_attribute_group`.`product_id`
SQL;

        // Todo
        if ($filter === 1) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) = 0 AND COUNT(`completeness_per_attribute_group`.`product_id`) = 0)
SQL;
        }

        // IN PROGRESS
        if ($filter === 2) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) > 0 OR COUNT(`completeness_per_attribute_group`.`product_id`) > 0)
AND SUM(`completeness_per_attribute_group`.`is_complete`) <> COUNT(`completeness_per_attribute_group`.`product_id`)
SQL;
        }

        // DONE
        if ($filter === 3) {
            $sql .= <<<SQL
HAVING (SUM(`completeness_per_attribute_group`.`is_complete`) = COUNT(`completeness_per_attribute_group`.`product_id`))
SQL;
        }

        $parameters = [
            'datagrid_view_id' => $viewId,
            'username' => $this->tokenStorage->getToken()->getUsername()
        ];

        $entityManager = $queryBuilder->getEntityManager();
        $connection = $entityManager->getConnection();
        $productIds = $connection->fetchAll($sql, $parameters);


        return  array_column($productIds, 'product_id');
    }
}
