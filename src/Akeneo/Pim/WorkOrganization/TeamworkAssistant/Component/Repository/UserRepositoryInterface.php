<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface UserRepositoryInterface extends ObjectRepository
{
    /**
     * Return users who are AT LEAST in one of the given $groupIds.
     *
     * @param ProjectInterface $project
     *
     * @return UserInterface[]
     */
    public function findUsersToNotify(ProjectInterface $project);

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     *
     * @return bool
     */
    public function isProjectContributor(ProjectInterface $project, UserInterface $user);
}
