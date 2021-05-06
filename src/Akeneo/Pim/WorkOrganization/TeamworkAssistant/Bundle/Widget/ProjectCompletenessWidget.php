<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Widget;

use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;

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
        return '';
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
