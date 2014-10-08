<?php

namespace Pim\Bundle\FilterBundle\Datasource\MongoDbOdm;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

/**
 * MongoDB ODM datasource adapter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OdmFilterDatasourceAdapter implements FilterDatasourceAdapterInterface
{
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
        $this->expressionBuilder = null;
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
        switch ($comparisonType) {
            case TextFilterType::TYPE_STARTS_WITH:
                $format = '/^%s/i';
                break;
            case TextFilterType::TYPE_ENDS_WITH:
                $format = '/%s$/i';
                break;
            case TextFilterType::TYPE_CONTAINS:
                $format = '/%s/i';
                break;
            case TextFilterType::TYPE_NOT_CONTAINS:
                $format = '/^((?!%s).)*$/i';
                break;
            default:
                $format = '%s';
        }

        return $format;
    }

    /**
     * Adds a new WHERE or HAVING restriction depends on the given parameters.
     *
     * @param mixed   $restriction The restriction to add.
     * @param string  $condition   Can be FilterUtility::CONDITION_OR or FilterUtility::CONDITION_AND.
     * @param boolean $isComputed  Specifies whether the restriction should be added to the HAVING part of a query.
     */
    public function addRestriction($restriction, $condition, $isComputed = false)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy($groupBy)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function addGroupBy($group)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function expr()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($key, $value, $type = null)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function generateParameterName($filterName)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * Returns a QueryBuilder object used to modify this data source
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }
}
