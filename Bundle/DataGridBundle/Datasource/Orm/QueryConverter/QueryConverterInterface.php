<?php

namespace Oro\Bundle\DataGridBundle\Datasource\Orm\QueryConverter;

use Doctrine\ORM\QueryBuilder;

interface QueryConverterInterface
{
    /**
     * Parses a YAML string to a QueryBuilder object.
     *
     * @param  string|array $value A YAML string or structured associative array
     * @param  QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function parse($value, QueryBuilder $qb);

    /**
     * Dumps a QueryBuilder object to YAML.
     *
     * @param  QueryBuilder $input
     *
     * @return string       The YAML representation of the PHP value
     */
    public function dump(QueryBuilder $input);
}
