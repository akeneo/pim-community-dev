<?php

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
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
     * @var SmartManagerRegistry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param SmartManagerRegistry $registry
     */
    public function __construct(SmartManagerRegistry $registry)
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
            ->getRepository('PimEnterprise\Bundle\CatalogBundle\Model\Proposal')
            ->findBy([], ['createdAt' => 'DESC'], 10);

        return [
            'params' => $proposals
        ];
    }
}
