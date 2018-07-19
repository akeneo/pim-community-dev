<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Factory;

use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Model\ProjectStatusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ProjectStatusFactoryInterface
{
    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     *
     * @return ProjectStatusInterface
     */
    public function create(ProjectInterface $project, UserInterface $user);
}
