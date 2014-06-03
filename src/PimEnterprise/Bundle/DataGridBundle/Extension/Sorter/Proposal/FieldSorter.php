<?php

namespace PimEnterprise\Bundle\DataGridBundle\Extension\Sorter\Proposal;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ProposalRepositoryInterface;

/**
 * Field sorter for proposals
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FieldSorter implements SorterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $repository = $datasource->getRepository();
        $qb         = $datasource->getQueryBuilder();
        $repository->applySorter($qb, $field, $direction);
    }
}
