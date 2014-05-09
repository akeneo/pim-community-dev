<?php

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

use Doctrine\Common\Persistence\ObjectManager;
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
     * @var ObjectManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
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
        $proposals = $this->manager
            ->getRepository('PimEnterprise\Bundle\CatalogBundle\Model\Proposal')
            ->findBy([], ['createdAt' => 'DESC'], 10);

        return [
            'params' => $proposals
        ];
    }
}
