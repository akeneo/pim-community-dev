<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamWorkAssistant\Event;

/**
 * List of the project creation events.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
final class ProjectEvents
{
    /**
     * This event is dispatched after the end of the job which calculates the project.
     * For instance, we need to calculate the user group affected the project.
     */
    const PROJECT_CALCULATED = 'team_work_assistant.project_calculated';

    /**
     * This event is dispatched once the whole project is created.
     */
    const PROJECT_CREATED = 'team_work_assistant.project_created';
}
