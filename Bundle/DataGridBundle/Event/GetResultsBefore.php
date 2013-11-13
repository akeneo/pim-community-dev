<?php

namespace Oro\Bundle\DataGridBundle\Event;

use Doctrine\ORM\Query;
use Symfony\Component\EventDispatcher\Event;


/**
 * Class GetResultsBefore
 * @package Oro\Bundle\DataGridBundle\Event
 *
 */
class GetResultsBefore extends Event
{
    const NAME = 'oro_datagrid.datgrid.get_results.before';

    /** @var Query */
    protected $query;

    /**
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }
}
