<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AttributeOptionRemovalSubscriber implements EventSubscriberInterface
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
            StorageEvents::POST_REMOVE => 'disableCatalogsIfAttributeOptionIsRemoved',
        ];
    }

    public function disableCatalogsIfAttributeOptionIsRemoved(GenericEvent $event): void
    {
        $attributeOption = $event->getSubject();
        if (!$attributeOption instanceof AttributeOptionInterface) {
            return;
        }

        $attributeCode = $attributeOption->getAttribute()->getCode();
        $attributeOptionCode = $attributeOption->getCode();

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('disable_catalog_on_attribute_option_removal');

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), [
            'attribute_code' => $attributeCode,
            'attribute_option_code' => $attributeOptionCode
        ]);
    }
}
