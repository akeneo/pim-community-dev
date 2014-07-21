<?php

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Widget to display product propositions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionWidget implements WidgetInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param UserContext     $userContext
     */
    public function __construct(ManagerRegistry $registry, UserContext $userContext)
    {
        $this->registry    = $registry;
        $this->userContext = $userContext;
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
            $isOwner = $this->registry
                ->getRepository('PimEnterprise\Bundle\SecurityBundle\Entity\CategoryAccess')
                ->isOwner($user);
        }

        if (!$isOwner) {
            return ['show' => false];
        }

        $propositions = $this->registry
            ->getRepository('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition')
            ->findBy(
                [
                    'status' => Proposition::READY
                ],
                ['createdAt' => 'desc'],
                10
            );

        return [
            'show'   => true,
            'params' => $propositions
        ];
    }
}
