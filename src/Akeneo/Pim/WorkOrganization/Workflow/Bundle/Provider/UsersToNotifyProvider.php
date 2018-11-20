<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * Class UsersToNotifyProvider
 *
 * Provides a set of users to be notified when proposals are created.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class UsersToNotifyProvider
{
    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get a set of users according to their group membership and their profile notifications options.
     *
     * @param int[] $groupIds
     *
     * @return UserInterface[]
     */
    public function getUsersToNotify(array $groupIds)
    {
        $users = $this->userRepository->findByGroupIds($groupIds);

        return array_filter($users, function ($user) {
            return $user->getProperty('proposals_to_review_notification');
        });
    }
}
