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
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Basic implementation of a product draft mass review tasklet
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
abstract class AbstractReviewTasklet implements TaskletInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ProductDraftRepositoryInterface */
    protected $draftRepository;

    /** @var ProductDraftManager */
    protected $productDraftManager;

    /** @var  UserRepositoryInterface */
    protected $userRepository;

    /** @var SecurityContextInterface */
    protected $securityContext;

    public function __construct(
        ProductDraftRepositoryInterface $draftRepository,
        ProductDraftManager $productDraftManager,
        UserRepositoryInterface $userRepository,
        SecurityContextInterface $securityContext
    ) {
        $this->draftRepository     = $draftRepository;
        $this->productDraftManager = $productDraftManager;
        $this->userRepository      = $userRepository;
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
        $user = $this->userRepository->findOneByIdentifier($username);

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
