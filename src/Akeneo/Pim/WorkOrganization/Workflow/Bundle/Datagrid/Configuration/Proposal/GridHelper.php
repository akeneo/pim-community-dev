<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Configuration\Proposal;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Helper for proposal datagrid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class GridHelper
{
    /** @var AuthorizationCheckerInterface */
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
     * Returns callback that will disable approve and refuse buttons given permissions on proposal
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $canReview = $this->permissionHelper->canEditOneChangeToReview($record->getValue('proposal'));
            $toReview = $record->getValue('proposal')->getStatus() === EntityWithValuesDraftInterface::READY;
            $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $record->getValue('product'));

            return [
                'approve' => $isOwner && $toReview && $canReview,
                'refuse'  => $isOwner && $toReview && $canReview
            ];
        };
    }
}
