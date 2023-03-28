<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRemovalSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly JobLauncherInterface $jobLauncher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'notifyIfAttributeIsRemoved',
        ];
    }

    public function notifyIfAttributeIsRemoved(GenericEvent $event): void
    {
        $attribute = $event->getSubject();

        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        /** @var JobInstance|null $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('notify_on_attribute_removal');

        if (!$jobInstance instanceof JobInstance) {
            return;
        }

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()?->getUser(), [
            'attribute_codes' => [$attribute->getCode()],
        ]);
    }
}
