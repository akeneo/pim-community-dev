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

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Query\GetAccessGroupIdsForLocaleCode;
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

    /** @var GetAccessGroupIdsForLocaleCode */
    protected $getAccessGroupIdsForLocaleCode;

    public function __construct(
        UserRepositoryInterface $userRepository,
        GetAccessGroupIdsForLocaleCode $getAccessGroupIdsForLocaleCode
    ) {
        $this->userRepository = $userRepository;
        $this->getAccessGroupIdsForLocaleCode = $getAccessGroupIdsForLocaleCode;
    }

    /**
     * Get a set of users according to their group membership and their profile notifications options.
     *
     * @param int[] $groupIds
     * @param array $filters
     * @return UserInterface[]
     */
    public function getUsersToNotify(array $groupIds, array $filters = [])
    {
        $users = $this->userRepository->findByGroupIds($groupIds);

        $users = array_filter($users, function ($user) {
            return $user->getProperty('proposals_to_review_notification');
        });

        if (is_array($filters['locales'] ?? null)) {
            $users = $this->filterByLocales($users, $filters['locales']);
        }

        return $users;
    }

    /**
     * Returns only the users that owns the products in given locale.
     * If a user belong to at least one group that owns the product in locale, we return it.
     *
     * @param UserInterface[] $users
     * @param string[] $locales
     * @return array
     */
    protected function filterByLocales(array $users, array $locales): array
    {
        if (empty($locales)) {
            return $users;
        }

        $groupIdsForLocales = [];
        foreach ($locales as $localeCode) {
            $groupIdsForLocales[$localeCode] = $this
                ->getAccessGroupIdsForLocaleCode
                ->getGrantedUserGroupIdsForLocaleCode($localeCode, Attributes::EDIT_ITEMS);
        }

        return array_filter($users, function (UserInterface $user) use ($locales, $groupIdsForLocales) {
            foreach ($locales as $localeCode) {
                $userGroupIds = $user->getGroupsIds();
                if (!empty(array_intersect($userGroupIds, $groupIdsForLocales[$localeCode]))) {
                    return true;
                }
            }

            return false;
        });
    }
}
