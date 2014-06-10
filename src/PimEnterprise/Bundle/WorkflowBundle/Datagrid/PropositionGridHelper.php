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
     * Returns callback that will disable approve and refuse buttons
     * if the proposition has already been approved or refused
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if (Proposition::WAITING !== $record->getValue('status')) {
                return ['approve' => false, 'refuse' => false];
            }
        };
    }

    /**
     * Returns available proposition status choices
     *
     * @return array
     */
    public function getStatusChoices()
    {
        return [
            Proposition::WAITING  => 'pimee_workflow.proposition.status.waiting',
            Proposition::APPROVED => 'pimee_workflow.proposition.status.approved',
            Proposition::REFUSED  => 'pimee_workflow.proposition.status.refused'
        ];
    }
}
