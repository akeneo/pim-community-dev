<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ProductDraft ownership repository interface
 *
 * @author Filips Alpe <filips@akeneo.com>
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
