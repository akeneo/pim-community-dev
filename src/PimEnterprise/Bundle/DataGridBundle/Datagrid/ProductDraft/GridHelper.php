<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductDraft;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Helper for product draft datagrid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class GridHelper
{
    /** @var SecurityContextInterface  */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * Returns callback that will disable approve and refuse buttons
     * given product draft status
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if (null !== $this->securityContext &&
                false === $this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $record->getRootEntity())
            ) {
                return ['approve' => false, 'refuse' => false, 'remove' => false];
            }

            if (ProductDraftInterface::IN_PROGRESS === $record->getValue('status')) {
                return ['approve' => false, 'refuse' => false];
            }

            return ['remove' => false];
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
            ProductDraftInterface::IN_PROGRESS => 'pimee_workflow.product_draft.status.in_progress',
            ProductDraftInterface::READY       => 'pimee_workflow.product_draft.status.ready',
        ];
    }
}
