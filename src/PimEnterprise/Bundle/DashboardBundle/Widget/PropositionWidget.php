<?php

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionOwnershipRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;

/**
 * Widget to display product propositions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionWidget implements WidgetInterface
{
    /**
     * @var CategoryAccessRepository
     */
    protected $accessRepository;

    /**
     * @var PropositionOwnershipRepositoryInterface
     */
    protected $ownershipRepository;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param CategoryAccessRepository                $accessRepository
     * @param PropositionOwnershipRepositoryInterface $ownershipRepository
     * @param UserContext                             $userContext
     */
    public function __construct(
        CategoryAccessRepository $accessRepository,
        PropositionOwnershipRepositoryInterface $ownershipRepository,
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
        return 'PimEnterpriseDashboardBundle:Widget:propositions.html.twig';
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

        $propositions = $this->ownershipRepository->findApprovableByUser($user, 10);

        return [
            'show'   => true,
            'params' => $propositions
        ];
    }
}
