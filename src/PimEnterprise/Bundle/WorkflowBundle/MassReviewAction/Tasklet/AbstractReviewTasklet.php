<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\MassReviewAction\Tasklet;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\BaseConnectorBundle\Step\TaskletInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Basic implementation of a product draft mass review tasklet
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

    /** @var ProductDraftRepositoryInterface */
    protected $draftRepository;

    /** @var ProductDraftManager */
    protected $productDraftManager;

    /** @var  UserProviderInterface */
    protected $userProvider;

    /** @var SecurityContextInterface */
    protected $securityContext;

    public function __construct(
        ProductDraftRepositoryInterface $draftRepository,
        ProductDraftManager $productDraftManager,
        UserProviderInterface $userProvider,
        SecurityContextInterface $securityContext
    ) {
        $this->draftRepository     = $draftRepository;
        $this->productDraftManager = $productDraftManager;
        $this->userProvider        = $userProvider;
        $this->securityContext     = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * Initialize the SecurityContext from the given $stepExecution
     *
     * @param StepExecution $stepExecution
     */
    protected function initSecurityContext(StepExecution $stepExecution)
    {
        $username = $stepExecution->getJobExecution()->getUser();
        $user = $this->userProvider->loadUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->securityContext->setToken($token);
    }

    /**
     * Increment skipped items counter and add a warning
     *
     * @param StepExecution $stepExecution
     * @param string        $reason
     * @param mixed         $item
     */
    protected function skipWithWarning(StepExecution $stepExecution, $name, $reason, array $reasonParameters, $item)
    {
        $stepExecution->incrementSummaryInfo('skip');
        $stepExecution->addWarning(
            $name,
            'pimee_workflow.product_draft.mass_review_action.error.' . $reason,
            $reasonParameters,
            $item
        );
    }
}
