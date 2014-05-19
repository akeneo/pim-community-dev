<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;

/**
 * Helper for proposal datagrid
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalGridHelper
{
    /**
     * Returns callback that will disable approve and refuse buttons
     * if the proposal has already been approved or refused
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if (Proposal::WAITING !== $record->getValue('status')) {
                return ['approve' => false, 'refuse' => false];
            }
        };
    }

    /**
     * Returns available proposal status choices
     *
     * @return array
     */
    public function getStatusChoices()
    {
        return [
            Proposal::WAITING  => 'pimee_workflow.proposal.status.waiting',
            Proposal::APPROVED => 'pimee_workflow.proposal.status.approved',
            Proposal::REFUSED  => 'pimee_workflow.proposal.status.refused'
        ];
    }
}
