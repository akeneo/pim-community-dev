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

use PimEnterprise\Bundle\SecurityBundle\Voter\ProductOwnerVoter;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Widget to display product product drafts
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductDraftWidget implements WidgetInterface
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ProductDraftOwnershipRepositoryInterface
     */
    protected $ownershipRepository;

    /**
     * Constructor
     *
     * @param SecurityContextInterface                 $securityContext
     * @param ProductDraftOwnershipRepositoryInterface $ownershipRepository
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ProductDraftOwnershipRepositoryInterface $ownershipRepository
    ) {
        $this->securityContext     = $securityContext;
        $this->ownershipRepository = $ownershipRepository;
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
        return ['show' => $this->isDisplayable()];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->isDisplayable()) {
            return [];
        }

        $user = $this->securityContext->getToken()->getUser();
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
     * Indicates if the widget should be displayed to the current user
     *
     * @return boolean
     */
    protected function isDisplayable()
    {
        return $this->securityContext->isGranted(ProductOwnerVoter::OWN);
    }
}
