<?php

namespace Akeneo\SharedCatalog\EventSubscriber;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JobInstancePublisherSubscriber implements EventSubscriber
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var string */
    private $sharedCatalogJobName;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        string $sharedCatalogJobName
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->sharedCatalogJobName = $sharedCatalogJobName;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof JobInstance) {
            return;
        }

        if ($entity->getJobName() !== $this->sharedCatalogJobName) {
            return;
        }

        if (null === $this->tokenStorage->getToken()) {
            return;
        }

        $rawParameters = $entity->getRawParameters();
        $rawParameters['publisher'] = $this->tokenStorage->getToken()->getUsername();
        $entity->setRawParameters($rawParameters);
    }
}
