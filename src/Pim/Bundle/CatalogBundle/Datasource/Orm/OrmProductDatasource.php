<?php
namespace Pim\Bundle\CatalogBundle\Datasource\Orm;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as OroOrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

/**
 * Product data source
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmProductDatasource extends OroOrmDatasource
{
    /**
     * @var string
     */
    const TYPE = 'orm_product';

    /**
     * {@inheritDoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $entity = $config['entity'];
        $repository = $this->em->getRepository($entity);
        $this->qb = $repository->createDatagridQueryBuilder();

        $grid->setDatasource(clone $this);
    }
}
