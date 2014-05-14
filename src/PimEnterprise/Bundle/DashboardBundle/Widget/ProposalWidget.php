<?php

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;

/**
 * Widget to display product proposals
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalWidget implements WidgetInterface
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
        return 'PimEnterpriseDashboardBundle:Widget:proposals.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $proposals = $this->registry
            ->getRepository('PimEnterprise\Bundle\WorkflowBundle\Model\Proposal')
            ->findBy([], ['createdAt' => 'DESC'], 10);

        return [
            'params' => $proposals
        ];
    }
}
