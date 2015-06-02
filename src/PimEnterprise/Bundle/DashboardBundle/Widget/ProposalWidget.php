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

use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Widget to display proposals
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProposalWidget implements WidgetInterface
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var UserManager */
    protected $userManager;

    /**
     * Constructor
     *
     * @param SecurityContextInterface        $securityContext
     * @param ProductDraftRepositoryInterface $ownershipRepository
     * @param UserManager                     $userManager
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ProductDraftRepositoryInterface $ownershipRepository,
        UserManager $userManager
    ) {
        $this->securityContext = $securityContext;
        $this->repository      = $ownershipRepository;
        $this->userManager     = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'proposals';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimEnterpriseDashboardBundle:Widget:proposal.html.twig';
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
        $proposals = $this->repository->findApprovableByUser($user, 10);

        foreach ($proposals as $proposal) {
            $result[] = [
                'productId'    => $proposal->getProduct()->getId(),
                'productLabel' => $proposal->getProduct()->getLabel(),
                'author'       => $this->getAuthorFullName($proposal->getAuthor()),
                'createdAt'    => $proposal->getCreatedAt()->format('U')
            ];
        }

        return $result;
    }

    /**
     * Indicates if the widget should be displayed to the current user
     *
     * @return bool
     */
    protected function isDisplayable()
    {
        return $this->securityContext->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY);
    }

    /**
     * Get author full name for given $authorUsername
     *
     * @param string $authorUsername
     *
     * @return string
     */
    protected function getAuthorFullName($authorUsername)
    {
        $user = $this->userManager->findUserByUsername($authorUsername);
        $authorName = $authorUsername;

        if ($user) {
            $authorName = sprintf('%s %s', $user->getFirstName(), $user->getLastName());
        }

        return $authorName;
    }
}
