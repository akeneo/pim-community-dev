<?php

namespace Akeneo\SharedCatalog\EventSubscriber;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class JobInstancePublisherSubscriber implements EventSubscriber
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private string $sharedCatalogJobName
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof JobInstance) {
            return;
        }

        if ($entity->getJobName() !== $this->sharedCatalogJobName) {
            return;
        }

        $rawParameters = $entity->getRawParameters();
        $rawParameters['publisher'] = $this->getPublisherEmail();
        $entity->setRawParameters($rawParameters);
    }

    private function getPublisherEmail(): ?string
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return null;
        }

        return $user->getEmail();
    }
}
