<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\ProductDraft;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Helper for product draft datagrid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class GridHelper
{
    /** @var AuthorizationCheckerInterface  */
    protected $authorizationChecker;

    /** @var ProductDraftChangesPermissionHelper */
    protected $permissionHelper;

    /**
     * @param AuthorizationCheckerInterface       $authorizationChecker
     * @param ProductDraftChangesPermissionHelper $permissionHelper
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftChangesPermissionHelper $permissionHelper
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->permissionHelper     = $permissionHelper;
    }

    /**
     * Returns callback that will disable approve and refuse buttons given product draft status and permissions
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $productDraft = $record->getRootEntity();

            $canReview = $this->permissionHelper->canEditOneChangeToReview($record->getRootEntity());
            $canDelete = $this->permissionHelper->canEditOneChangeDraft($record->getRootEntity());

            $toReview = $productDraft->getStatus() === ProductDraftInterface::READY;
            $inProgress = $productDraft->isInProgress();
            $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productDraft->getProduct());

            return [
                'approve' => $toReview && $isOwner && $canReview,
                'refuse'  => $toReview && $isOwner && $canReview,
                'remove'  => $inProgress && $isOwner && $canDelete
            ];
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
