<?php

declare(strict_types=1);

namespace Specification\AkeneoEnterprise\Pim\Enrichment\Category\Infrastructure\Install\Subscriber;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\Group;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveUserGroupAllFromCategoriesSubscriberSpec extends ObjectBehavior
{
    function let(
        CategoryAccessRepository $categoryAccessRepository,
        GroupRepositoryInterface $groupRepository,
        ObjectManager $objectManager,
        FeatureFlags $featureFlags
    ) {
        $this->beConstructedWith($categoryAccessRepository, $groupRepository, $objectManager, $featureFlags);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_removes_group_all_from_category_accesses_when_permission_activated_after_install(
        CategoryAccessRepository $categoryAccessRepository,
        GroupRepositoryInterface $groupRepository,
        ObjectManager $objectManager,
        FeatureFlags $featureFlags
    ) {
        $featureFlags->isEnabled('permission')->willReturn(true);
        $group = new Group();
        $groupRepository->getDefaultUserGroup()->willReturn($group);
        $categoryAccessRepository->revokeAccessToGroups([$group])->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->execute();
    }

    function it_does_not_remove_group_all_from_category_accesses_when_permission_not_activated(
        ObjectManager $objectManager,
        FeatureFlags $featureFlags
    ) {
        $featureFlags->isEnabled('permission')->willReturn(false);
        $objectManager->flush()->shouldNotBeCalled();

        $this->execute();
    }
}
