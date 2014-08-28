<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;

/**
 * Widget to display product product drafts
 *
 * @author    Filips Alpe <filips@akeneo.com>
 */
class ProductDraftWidget implements WidgetInterface
{
    /**
     * @var CategoryAccessRepository
     */
    protected $accessRepository;

    /**
     * @var ProductDraftOwnershipRepositoryInterface
     */
    protected $ownershipRepository;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param CategoryAccessRepository                 $accessRepository
     * @param ProductDraftOwnershipRepositoryInterface $ownershipRepository
     * @param UserContext                              $userContext
     */
    public function __construct(
        CategoryAccessRepository $accessRepository,
        ProductDraftOwnershipRepositoryInterface $ownershipRepository,
        UserContext $userContext
    ) {
        $this->accessRepository    = $accessRepository;
        $this->ownershipRepository = $ownershipRepository;
        $this->userContext         = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimEnterpriseDashboardBundle:Widget:product_drafts.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $user    = $this->userContext->getUser();
        $isOwner = false;

        if (null !== $user) {
            $isOwner = $this->accessRepository->isOwner($user);
        }

        if (!$isOwner) {
            return ['show' => false];
        }

        $productDrafts = $this->ownershipRepository->findApprovableByUser($user, 10);

        return [
            'show'   => true,
            'params' => $productDrafts
        ];
    }
}
