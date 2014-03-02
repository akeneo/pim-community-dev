<?php

namespace Pim\Bundle\DataGridBundle\Datasource\Orm;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as OroOrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ContextConfigurator;

/**
 * Basic PIM data source, allow to prepare query builder from repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmDatasource extends OroOrmDatasource
{
    /**
     * @var string
     */
    const TYPE = 'pim_orm';

    /**
     * @var string
     */
    const ENTITY_PATH = '[source][entity]';

    /**
     * {@inheritdoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        if (!isset($config['entity'])) {
            throw new \Exception(get_class($this).' expects to be configured with entity');
        }

        $entity = $config['entity'];
        $repository = $this->em->getRepository($entity);

        if (isset($config['repository_method']) && $method = $config['repository_method']) {
            $this->qb = $repository->$method();
        } else {
            $this->qb = $repository->createQueryBuilder('o');
        }

        $grid->setDatasource(clone $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $query = $this->qb->getQuery();

        $results = $query->execute();
        $rows    = [];
        foreach ($results as $result) {
            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }

    /**
     * We update the query to count, get ids and fetch data, so, we can lost expected query builder parameters,
     * and we have to remove them
     *
     * @param QueryBuilder $qb
     */
    public static function removeExtraParameters(QueryBuilder $qb)
    {
        $parameters    = $qb->getParameters();
        $dql           = $qb->getDQL();
        foreach ($parameters as $parameter) {
            if (strpos($dql, ':'.$parameter->getName()) === false) {
                $parameters->removeElement($parameter);
            }
        }
        $qb->setParameters($parameters);
    }
}
