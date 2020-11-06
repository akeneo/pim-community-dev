<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Notification;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ProjectNotifierInterface
{
    /**
     * Notify the user about project event.
     *
     * @return bool
     */
    public function notifyUser(
        UserInterface $user,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness
    );
}
