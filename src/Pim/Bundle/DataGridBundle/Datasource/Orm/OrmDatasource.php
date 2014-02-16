<?php

namespace Pim\Bundle\DataGridBundle\Datasource\Orm;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as OroOrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;

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
     * @var boolean
     */
    protected $isFlexible = false;

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

        $this->isFlexible = isset($config['is_flexible']) ? (bool) $config['is_flexible'] : false;

        $grid->setDatasource(clone $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $query = $this->qb->getQuery();

        if ($this->isFlexible) {
            $results = $query->getArrayResult();
            $rows    = [];
            foreach ($results as $result) {
                $entityFields = $result[0];
                unset($result[0]);
                $otherFields = $result;
                $result = $entityFields + $otherFields;
                $values = $result['values'];
                foreach ($values as $value) {
                    $result[$value['attribute']['code']]= $value;
                }
                unset($result['values']);
                $rows[] = new ResultRecord($result);
            }

        } else {
            $results = $query->execute();
            $rows    = [];
            foreach ($results as $result) {
                $rows[] = new ResultRecord($result);
            }

            return $rows;
        }

        return $rows;
    }
}
