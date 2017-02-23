<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamWorkAssistantBundle\Widget;

use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\ProjectRepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Widget to display project progress
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectCompletenessWidget implements WidgetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'project_progress';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimEnterpriseTeamWorkAssistantBundle:Widget:completeness.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return [];
    }
}
