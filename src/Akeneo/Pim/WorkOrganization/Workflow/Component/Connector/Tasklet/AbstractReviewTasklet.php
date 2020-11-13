<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Tasklet;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Basic implementation of draft mass review tasklet
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
abstract class AbstractReviewTasklet implements TaskletInterface
{
    /** @staticvar string */
    const ERROR_DRAFT_NOT_READY = 'draft_not_ready';

    /** @staticvar string */
    const ERROR_NOT_PRODUCT_OWNER = 'not_product_owner';

    /** @staticvar string */
    const ERROR_CANNOT_EDIT_ATTR = 'cannot_edit_attributes';

    /** @staticvar string */
    const ERROR_INVALID_DRAFT = 'invalid_draft';

    protected StepExecution $stepExecution;
    protected EntityWithValuesDraftRepositoryInterface $productDraftRepository;
    protected EntityWithValuesDraftManager $productDraftManager;
    protected EntityWithValuesDraftRepositoryInterface $productModelDraftRepository;
    protected EntityWithValuesDraftManager $productModelDraftManager;
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected ProductDraftChangesPermissionHelper $permissionHelper;
    protected JobRepositoryInterface $jobRepository;
    protected JobStopper $jobStopper;

    public function __construct(
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftManager $productDraftManager,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        EntityWithValuesDraftManager $productModelDraftManager,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftChangesPermissionHelper $permissionHelper,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper
    ) {
        $this->productDraftRepository = $productDraftRepository;
        $this->productDraftManager = $productDraftManager;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->productModelDraftManager = $productModelDraftManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->permissionHelper = $permissionHelper;
        $this->jobRepository = $jobRepository;
        $this->jobStopper = $jobStopper;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): AbstractReviewTasklet
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * Increment skipped items counter and add a warning
     *
     * @param StepExecution $stepExecution
     * @param string        $name
     * @param string        $reason
     * @param array         $reasonParameters
     * @param mixed         $item
     */
    protected function skipWithWarning(StepExecution $stepExecution, $name, $reason, array $reasonParameters, $item): void
    {
        $stepExecution->incrementSummaryInfo('skip');
        $stepExecution->incrementProcessedItems();
        $stepExecution->addWarning(
            'pimee_workflow.product_draft.mass_review_action.error.' . $reason,
            $reasonParameters,
            new DataInvalidItem($item)
        );
    }
}
