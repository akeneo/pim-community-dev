<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\EventSubscriber;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryRemovalSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private TokenStorageInterface $tokenStorage,
        private JobLauncherInterface $jobLauncher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'disableCatalogsIfCategoryIsRemoved',
        ];
    }

    public function disableCatalogsIfCategoryIsRemoved(GenericEvent $event): void
    {
        $category = $event->getSubject();
        if (!$category instanceof CategoryInterface) {
            return;
        }

        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('disable_catalog_on_category_removal');

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()?->getUser(), [
            'category_code' => $category->getCode(),
        ]);
    }
}
