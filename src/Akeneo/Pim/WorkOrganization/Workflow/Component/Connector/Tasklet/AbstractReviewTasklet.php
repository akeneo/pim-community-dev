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
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

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

    /** @var StepExecution */
    protected $stepExecution;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepository;

    /** @var EntityWithValuesDraftManager */
    protected $productDraftManager;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productModelDraftRepository;

    /** @var EntityWithValuesDraftManager */
    protected $productModelDraftManager;

    /** @var  UserProviderInterface */
    protected $userProvider;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var AuthorizationCheckerInterface */
    protected $tokenStorage;

    /** @var ProductDraftChangesPermissionHelper */
    protected $permissionHelper;

    /**
     * @param EntityWithValuesDraftRepositoryInterface $productDraftRepository
     * @param EntityWithValuesDraftManager             $productDraftManager
     * @param EntityWithValuesDraftRepositoryInterface $productModelDraftRepository
     * @param EntityWithValuesDraftManager             $productModelDraftManager
     * @param UserProviderInterface                    $userProvider
     * @param AuthorizationCheckerInterface            $authorizationChecker
     * @param TokenStorageInterface                    $tokenStorage
     * @param ProductDraftChangesPermissionHelper      $permissionHelper
     *
     * @todo merge : remove properties $userManager and $tokenStorage in master branch. They are no longer used.
     */
    public function __construct(
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftManager $productDraftManager,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        EntityWithValuesDraftManager $productModelDraftManager,
        UserProviderInterface $userProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        ProductDraftChangesPermissionHelper $permissionHelper
    ) {
        $this->productDraftRepository = $productDraftRepository;
        $this->productDraftManager = $productDraftManager;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->productModelDraftManager = $productModelDraftManager;
        $this->userProvider = $userProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->permissionHelper = $permissionHelper;
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
     * @deprecated will be removed in 3.0
     *
     * @todo merge : remove this method in master branch. It's no longer used
     */
    protected function initSecurityContext(StepExecution $stepExecution): void
    {
        $username = $stepExecution->getJobExecution()->getUser();
        $user = $this->userProvider->loadUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
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
        $stepExecution->addWarning(
            'pimee_workflow.product_draft.mass_review_action.error.' . $reason,
            $reasonParameters,
            new DataInvalidItem($item)
        );
    }
}
