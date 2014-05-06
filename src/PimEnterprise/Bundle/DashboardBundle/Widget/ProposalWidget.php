<?php

namespace PimEnterprise\Bundle\DashboardBundle\Widget;

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
        $params = [];

        return [
            'params' => $params
        ];
    }
}
