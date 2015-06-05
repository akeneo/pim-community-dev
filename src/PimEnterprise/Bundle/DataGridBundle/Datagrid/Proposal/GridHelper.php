<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Helper for proposal datagrid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class GridHelper
{
    /** @var UserManager $userManager */
    protected $userManager;

    /** @var SecurityContextInterface  */
    protected $securityContext;

    /**
     * @param UserManager              $userManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(UserManager $userManager, SecurityContextInterface $securityContext)
    {
        $this->userManager     = $userManager;
        $this->securityContext = $securityContext;
    }
    /**
     * Returns available proposal author choices
     *
     * @return array
     */
    public function getAuthorChoices()
    {
        $users = $this->userManager->getRepository()->findAll();

        $choices = [];

        foreach ($users as $user) {
            $choices[$user->getUsername()] = $user->getUsername();
        }

        return $choices;
    }

    /**
     * Returns callback that will disable approve and refuse buttons
     * given proposal status
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if (null !== $this->securityContext &&
                false === $this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $record->getRootEntity())
            ) {
                return ['approve' => false, 'refuse' => false];
            }
        };
    }
}
