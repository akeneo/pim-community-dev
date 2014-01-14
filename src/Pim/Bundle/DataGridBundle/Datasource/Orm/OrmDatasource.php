<?php

namespace Pim\Bundle\DataGridBundle\Datasource\Orm;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource As OroOrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\QueryConverter\YamlConverter;
use Pim\Bundle\DataGridBundle\Model\DatagridRepositoryInterface;

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
     * {@inheritDoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        if (!isset($config['entity'])) {
            throw new \Exception(get_class($this).' expects to be configured with entity');
        }

        $entity = $config['entity'];
        $repository = $this->em->getRepository($entity);
        if ($repository instanceof DatagridRepositoryInterface) {
            $this->qb = $repository->createDatagridQueryBuilder();
        } else {
            $this->qb = $repository->createQueryBuilder('o');
        }

        $queryConfig = array_intersect_key($config, array_flip(['query']));

        if (!empty($queryConfig)) {
            $converter = new YamlConverter();
            $this->qb  = $converter->parse($queryConfig, $this->qb);
        }

        $grid->setDatasource(clone $this);
    }
}
