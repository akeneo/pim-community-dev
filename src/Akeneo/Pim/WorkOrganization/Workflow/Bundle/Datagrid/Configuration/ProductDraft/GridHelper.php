<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Configuration\ProductDraft;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
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
        $this->permissionHelper = $permissionHelper;
    }

    /**
     * Returns callback that will disable approve and refuse buttons given product draft status and permissions
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $productDraft = $record->getValue('proposal');

            $canReview = $this->permissionHelper->canEditOneChangeToReview($productDraft);
            $canDelete = $this->permissionHelper->canEditOneChangeDraft($productDraft);

            $toReview = $productDraft->getStatus() === EntityWithValuesDraftInterface::READY;
            $inProgress = $productDraft->isInProgress();
            $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productDraft->getEntityWithValue());

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
            EntityWithValuesDraftInterface::IN_PROGRESS => 'pimee_workflow.product_draft.status.in_progress',
            EntityWithValuesDraftInterface::READY       => 'pimee_workflow.product_draft.status.ready',
        ];
    }
}
