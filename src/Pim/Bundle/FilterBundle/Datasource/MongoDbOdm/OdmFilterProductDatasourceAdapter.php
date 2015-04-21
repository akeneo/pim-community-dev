<?php

namespace Pim\Bundle\FilterBundle\Datasource\MongoDbOdm;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterProductDatasourceAdapterInterface;

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

    /**
     * Constructor
     *
     * @param DatasourceInterface $datasource
     */
    public function __construct(DatasourceInterface $datasource)
    {
        $this->qb  = $datasource->getQueryBuilder();
        $this->pqb = $datasource->getProductQueryBuilder();
    }

    /**
     * Return value format depending on comparison type
     *
     * @param string $comparisonType
     *
     * @return string
     */
    public function getFormatByComparisonType($comparisonType)
    {
        return '%s';
    }

    /**
     * {@inheritdoc}
     */
    public function getProductQueryBuilder()
    {
        return $this->pqb;
    }
}
