<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Query\GetAccessGroupIdsForLocaleCode;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UsersToNotifyProviderSpec extends ObjectBehavior
{
    public function let(
        UserRepositoryInterface $userRepository,
        GetAccessGroupIdsForLocaleCode $getAccessGroupIdsForLocaleCode
    ) {
        $this->beConstructedWith($userRepository, $getAccessGroupIdsForLocaleCode);
    }

    function it_returns_users_by_group_ids_and_by_notification_property(UserRepositoryInterface $userRepository)
    {
        $user1 = new User();
        $user1->addProperty('proposals_to_review_notification', true);
        $user2 = new User();
        $user2->addProperty('proposals_to_review_notification', true);
        $user3 = new User();
        $user3->addProperty('proposals_to_review_notification', false);
        $user4 = new User();
        $userRepository->findByGroupIds([1, 2])->willReturn([$user1, $user2, $user3, $user4]);

        $this->getUsersToNotify([1, 2])->shouldReturn([$user1, $user2]);
    }

    function it_returns_users_filtered_by_notification_property_and_locale_permission(
        UserRepositoryInterface $userRepository,
        GetAccessGroupIdsForLocaleCode $getAccessGroupIdsForLocaleCode
    ) {
        $user1 = new User();
        $user1->addProperty('proposals_to_review_notification', true);
        $user2 = new User();
        $user2->addProperty('proposals_to_review_notification', true);
        $user3 = new User();
        $user3->addProperty('proposals_to_review_notification', false);
        $user4 = new User();
        $userRepository->findByGroupIds([1, 2])->willReturn([$user1, $user2, $user3, $user4]);

        $group = new class extends Group implements GroupInterface {
            public function setId($id): void
            {
                $this->id = $id;
            }
        };
        $group->setId(1);

        $user1->addGroup($group);
        $user3->addGroup($group);
        $user4->addGroup($group);

        $getAccessGroupIdsForLocaleCode->getGrantedUserGroupIdsForLocaleCode('fr_FR', Attributes::EDIT_ITEMS)
            ->willReturn([1]);

        $this->getUsersToNotify([1, 2], ['locales' => ['fr_FR']])->shouldReturn([$user1]);
    }

    function it_returns_users_filtered_by_notification_property_and_several_locale_permissions(
        UserRepositoryInterface $userRepository,
        GetAccessGroupIdsForLocaleCode $getAccessGroupIdsForLocaleCode
    ) {
        $user1 = new User();
        $user1->addProperty('proposals_to_review_notification', true);
        $user2 = new User();
        $user2->addProperty('proposals_to_review_notification', true);
        $user3 = new User();
        $user3->addProperty('proposals_to_review_notification', false);
        $user4 = new User();
        $userRepository->findByGroupIds([1, 2])->willReturn([$user1, $user2, $user3, $user4]);

        $group1 = new class extends Group implements GroupInterface {
            public function setId($id): void
            {
                $this->id = $id;
            }
        };
        $group1->setId(1);
        $group2 = new class extends Group implements GroupInterface {
            public function setId($id): void
            {
                $this->id = $id;
            }
        };
        $group2->setId(2);

        $user1->addGroup($group1);
        $user2->addGroup($group2);
        $user3->addGroup($group1);
        $user4->addGroup($group1);

        $getAccessGroupIdsForLocaleCode->getGrantedUserGroupIdsForLocaleCode('fr_FR', Attributes::EDIT_ITEMS)
            ->willReturn([1]);
        $getAccessGroupIdsForLocaleCode->getGrantedUserGroupIdsForLocaleCode('en_US', Attributes::EDIT_ITEMS)
            ->willReturn([2]);

        $this->getUsersToNotify([1, 2], ['locales' => ['fr_FR', 'en_US']])->shouldReturn([$user1, $user2]);
    }
}
