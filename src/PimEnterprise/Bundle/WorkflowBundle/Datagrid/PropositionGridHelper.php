<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Helper for proposition datagrid
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionGridHelper
{
    /**
     * Returns available proposition status choices
     *
     * @return array
     */
    public function getStatusChoices()
    {
        return [
            Proposition::IN_PROGRESS => 'pimee_workflow.proposition.status.in_progress',
            Proposition::READY => 'pimee_workflow.proposition.status.ready',
        ];
    }
}
