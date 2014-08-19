<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ProductDraft ownership repository interface
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ProductDraftOwnershipRepositoryInterface
{
    /**
     * Return product drafts that can be approved by the given user
     *
     * @param UserInterface $user
     * @param integer       $limit
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft[]
     */
    public function findApprovableByUser(UserInterface $user, $limit = null);
}
