<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\Form;

interface FilterInterface
{
    const CONDITION_OR  = 'OR';
    const CONDITION_AND = 'AND';

    /**
     * Initialize current filter by config
     *
     * @param string $name
     * @param array  $params
     *
     * @return void
     */
    public function init($name, array $params);

    /**
     * Returns filter frontend name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns form for validation of current submitted filter data
     *
     * @return Form
     */
    public function getForm();

    /**
     * Applies filter to query builder
     *
     * @param QueryBuilder $qb
     * @param mixed        $value
     *
     * @return mixed
     */
    public function apply(QueryBuilder $qb, $value);
}
