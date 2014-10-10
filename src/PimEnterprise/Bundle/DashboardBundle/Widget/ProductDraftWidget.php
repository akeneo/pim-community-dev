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

use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Widget to display product product drafts
 *
 * @author Filips Alpe <filips@akeneo.com>
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
    public function getAlias()
    {
        return 'product_drafts';
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
        return ['show' => $this->isDisplayable($this->userContext->getUser())];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $user = $this->userContext->getUser();

        if (!$this->isDisplayable($user)) {
            return [];
        }

        $result = [];
        $productDrafts = $this->ownershipRepository->findApprovableByUser($user, 10);

        foreach ($productDrafts as $draft) {
            $result[] = [
                'productId'    => $draft->getProduct()->getId(),
                'productLabel' => $draft->getProduct()->getLabel(),
                'author'       => $draft->getAuthor(),
                'createdAt'    => $draft->getCreatedAt()->format('U')
            ];
        }

        return $result;
    }

    /**
     * Check if the widget should be displayed to the given user
     *
     * @param UserInterface $user
     *
     * @return boolean
     */
    protected function isDisplayable(UserInterface $user = null)
    {
        if (null === $user) {
            return false;
        }

        return $this->accessRepository->isOwner($user);
    }
}
