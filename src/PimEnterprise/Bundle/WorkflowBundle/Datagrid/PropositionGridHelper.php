<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid;

use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

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
     * given proposition status
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if (Proposition::IN_PROGRESS === $record->getValue('status')) {
                return ['approve' => false, 'refuse' => false];
            } else {
                return ['remove' => false];
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
            Proposition::IN_PROGRESS => 'pimee_workflow.proposition.status.in_progress',
            Proposition::READY => 'pimee_workflow.proposition.status.ready',
        ];
    }
}
