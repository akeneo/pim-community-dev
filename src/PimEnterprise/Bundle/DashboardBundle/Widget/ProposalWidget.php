<?php

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
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
     * Constructor
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
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
        $propositions = $this->registry
            ->getRepository('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition')
            ->findBy(['status' => Proposition::WAITING], ['createdAt' => 'desc'], 10);

        return [
            'params' => $propositions
        ];
    }
}
