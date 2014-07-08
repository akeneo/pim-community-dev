<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Oro\Bundle\UserBundle\Entity\User;

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
     * @param User    $user
     * @param integer $limit
     *
     * @return Proposition[]
     */
    public function findApprovableByUser(User $user, $limit = null);
}
