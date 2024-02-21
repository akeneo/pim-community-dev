<?php

namespace Oro\Bundle\PimFilterBundle\Datasource\Orm;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterProductDatasourceAdapterInterface;

/**
 * Customize the OroPlatform datasource adapter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmFilterProductDatasourceAdapter extends OrmFilterDatasourceAdapter implements
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
        $this->qb = $datasource->getQueryBuilder();
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
}
