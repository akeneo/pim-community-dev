<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Proposition ownership repository interface
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface PropositionOwnershipRepositoryInterface
{
    /**
     * Return propositions that can be approved by the given user
     *
     * @param UserInterface $user
     * @param integer       $limit
     *
     * @return Proposition[]
     */
    public function findApprovableByUser(UserInterface $user, $limit = null);
}
