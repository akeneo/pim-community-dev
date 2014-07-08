<?php

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionOwnershipRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryOwnershipRepository;

/**
 * Widget to display product propositions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionWidget implements WidgetInterface
{
    /**
     * @var CategoryOwnershipRepository
     */
    protected $catOwnershipRepo;

    /**
     * @var PropositionOwnershipRepositoryInterface
     */
    protected $propOwnershipRepo;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param CategoryOwnershipRepository             $catOwnershipRepo
     * @param PropositionOwnershipRepositoryInterface $propOwnershipRepo
     * @param UserContext                             $userContext
     */
    public function __construct(
        CategoryOwnershipRepository $catOwnershipRepo,
        PropositionOwnershipRepositoryInterface $propOwnershipRepo,
        UserContext $userContext
    ) {
        $this->catOwnershipRepo  = $catOwnershipRepo;
        $this->propOwnershipRepo = $propOwnershipRepo;
        $this->userContext       = $userContext;
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
            $isOwner = $this->catOwnershipRepo->isOwner($user);
        }

        if (!$isOwner) {
            return ['show' => false];
        }

        $propositions = $this->propOwnershipRepo->findApprovableByUser($user, 10);

        return [
            'show'   => true,
            'params' => $propositions
        ];
    }
}
