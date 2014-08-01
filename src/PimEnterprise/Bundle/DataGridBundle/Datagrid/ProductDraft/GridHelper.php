<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductDraft;

use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

/**
 * Helper for product draft datagrid
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class GridHelper
{
    /**
     * Returns callback that will disable approve and refuse buttons
     * given product draft status
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if (ProductDraft::IN_PROGRESS === $record->getValue('status')) {
                return ['approve' => false, 'refuse' => false];
            } else {
                return ['remove' => false];
            }
        };
    }

    /**
     * Returns available product draft status choices
     *
     * @return array
     */
    public function getStatusChoices()
    {
        return [
            ProductDraft::IN_PROGRESS => 'pimee_workflow.product_draft.status.in_progress',
            ProductDraft::READY => 'pimee_workflow.product_draft.status.ready',
        ];
    }
}
