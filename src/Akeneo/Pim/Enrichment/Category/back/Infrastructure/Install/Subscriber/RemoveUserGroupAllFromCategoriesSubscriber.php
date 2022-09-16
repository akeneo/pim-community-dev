<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Pim\Enrichment\Category\Infrastructure\Install\Subscriber;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeGroupAccessRepository;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Any new category group is by default in the Group All. This is useful:
 * - when permission are deactivated (like in Growth Edition), as user have permission on everything under the hood (he is owner of everything)
 * - in Serenity Edition, to make the category available for everyone by default
 * - when activating permission (Growth Edition upgraded in Serenity Edition). In that case, all categories are still visible without any impact.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveUserGroupAllFromCategoriesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CategoryAccessRepository $categoryAccessRepository,
        private GroupRepositoryInterface $groupRepository,
        private ObjectManager $objectManager,
        private FeatureFlags $featureFlags
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURES => ['execute'],
        ];
    }


    public function execute(): void
    {
        if ($this->featureFlags->isEnabled('permission')) {
            $groupAll = $this->groupRepository->getDefaultUserGroup();
            $this->categoryAccessRepository->revokeAccessToGroups([$groupAll]);
            $this->objectManager->flush();
        }
    }
}
