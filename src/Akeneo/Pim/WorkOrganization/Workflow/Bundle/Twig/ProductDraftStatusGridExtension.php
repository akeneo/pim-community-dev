<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ProductDraftStatusGridExtension extends \Twig_Extension
{
    /** @var ProductDraftChangesPermissionHelper */
    protected $permissionHelper;

    /**
     * @param ProductDraftChangesPermissionHelper $permissionHelper
     */
    public function __construct(ProductDraftChangesPermissionHelper $permissionHelper)
    {
        $this->permissionHelper = $permissionHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'get_draft_status_grid',
                [$this, 'getDraftStatusGrid'],
                ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * Get the human readable draft status for the grid
     *
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @return string
     */
    public function getDraftStatusGrid(EntityWithValuesDraftInterface $productDraft)
    {
        $toReview = $productDraft->getStatus() === EntityWithValuesDraftInterface::READY;
        $canReview = $this->permissionHelper->canEditOneChangeToReview($productDraft);
        $canDelete = $this->permissionHelper->canEditOneChangeDraft($productDraft);
        $canReviewAll = $this->permissionHelper->canEditAllChangesToReview($productDraft);

        if ($toReview) {
            if ($canReviewAll) {
                return 'ready';
            }

            if ($canReview) {
                return 'can_be_partially_reviewed';
            }

            return 'can_not_be_approved';
        }

        if ($canDelete) {
            return 'status.in_progress';
        }

        return 'can_not_be_deleted';
    }
}
