<?php

namespace Pim\Bundle\FilterBundle\Datasource\MongoDbOdm;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\FilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

/**
 * MongoDB ODM datasource adapter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OdmFilterProductDatasourceAdapter extends OdmFilterDatasourceAdapter implements
    FilterProductDatasourceAdapterInterface
{
    /** @var ProductQueryBuilderInterface */
    protected $pqb;

    /** @var QueryBuilder */
    protected $qb;

    /** @var OrmExpressionBuilder */
    protected $expressionBuilder;

    /**
     * Constructor
     *
     * @param DatasourceInterface $datasource
     */
    public function __construct(DatasourceInterface $datasource)
    {
        $this->qb  = $datasource->getQueryBuilder();
        $this->pqb = $datasource->getProductQueryBuilder();
        $this->expressionBuilder = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductQueryBuilder()
    {
        return $this->pqb;
    }
}
