<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

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
            if (null !== $record->getValue('status')) {
                return ['approve' => false, 'refuse' => false];
            }
        };
    }
}
