<?php

namespace Oro\Bundle\FilterBundle\Datasource;

/**
 * Allows a filter to modify a data source
 */
interface FilterDatasourceAdapterInterface
{
    /**
     * Adds a restriction to a data source.
     *
     * @param mixed  $restriction The restriction to add.
     * @param string $condition   The condition.
     *                            Can be FilterUtility::CONDITION_OR or FilterUtility::CONDITION_AND.
     * @param bool   $isComputed  Indicates whether a restriction is related to a computed part of a datasource.
     *                            For an example for ORM datasource it means that a restriction should be added
     *                            to the HAVING part of a query.
     */
    public function addRestriction($restriction, $condition, $isComputed = false);

    /**
     * Specifies a grouping over the results of a data source.
     * Replaces any previously specified groupings, if any.
     *
     * @param mixed $_ The grouping expression arguments.
     */
    public function groupBy($_);

    /**
     * Adds a grouping expression to a data source.
     * New grouping expression is added to previously specified groupings, if any.
     *
     * @param mixed $_ The grouping expression arguments.
     */
    public function addGroupBy($_);

    /**
     * Gets an expression builder object used for object-oriented construction of datasource restrictions
     *
     * @return ExpressionBuilderInterface
     */
    public function expr();

    /**
     * Sets a parameter for a data source being constructed.
     *
     * @param string|integer $key   The parameter position or name.
     * @param mixed          $value The parameter value.
     * @param string|null    $type  The parameter type.
     */
    public function setParameter($key, $value, $type = null);

    /**
     * Generates unique parameter name
     *
     * @param string $filterName A filter name
     * @return string
     */
    public function generateParameterName($filterName);
}
