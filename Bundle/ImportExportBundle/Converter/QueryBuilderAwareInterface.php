<?php

namespace Oro\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface used to specify whether need to set query builder to converter to perform additional adjustments
 */
interface QueryBuilderAwareInterface
{
    /**
     * Set query builder to converter
     *
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder);
}
