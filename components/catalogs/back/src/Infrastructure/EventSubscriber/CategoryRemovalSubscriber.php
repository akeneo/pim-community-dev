<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\EventSubscriber;

use Akeneo\Catalogs\Infrastructure\Persistence\Category\GetAllCategoryCodesFromParentCategoryCodeQuery;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryRemovalSubscriber implements EventSubscriberInterface
{
    /** @var string[] */
    private array $categoryCodes = [];

    public function __construct(
        private IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private TokenStorageInterface $tokenStorage,
        private JobLauncherInterface $jobLauncher,
        private GetAllCategoryCodesFromParentCategoryCodeQuery $getAllCategoryCodesFromParentCategoryCodeQuery,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'holdCategoryCodes',
            StorageEvents::PRE_REMOVE_ALL => 'holdCategoryCodes',
            StorageEvents::POST_REMOVE => 'disableCatalogsIfCategoryIsRemoved',
            StorageEvents::POST_REMOVE_ALL => 'disableCatalogsIfCategoryIsRemoved',
        ];
    }

    /**
     * We can not get the child's codes of a category after delete. So when a category is going to be deleted,
     * we get all the child's codes linked to it and keep them in $computedCodes property.
     */
    public function holdCategoryCodes(GenericEvent $event): void
    {
        $category = $event->getSubject();
        if (!$category instanceof CategoryInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->categoryCodes = $this->getAllCategoryCodesFromParentCategoryCodeQuery->execute($category->getCode());
    }

    public function disableCatalogsIfCategoryIsRemoved(GenericEvent $event): void
    {
        $category = $event->getSubject();
        if (!$category instanceof CategoryInterface) {
            return;
        }

        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('disable_catalogs_on_category_removal');

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()?->getUser(), [
            'category_codes' => $this->categoryCodes,
        ]);
    }
}
