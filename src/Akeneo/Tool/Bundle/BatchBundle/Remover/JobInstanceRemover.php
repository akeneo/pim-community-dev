<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Remover;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement\DeleteRunningUser;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\RemovableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class JobInstanceRemover implements RemoverInterface, BulkRemoverInterface
{
    public function __construct(
        private RemovableObjectRepositoryInterface $jobInstanceRepository,
        private EventDispatcherInterface $eventDispatcher,
        private DeleteRunningUser $deleteRunningUser,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param JobInstance $object
     */
    public function remove(mixed $object, array $options = []): void
    {
        $options['unitary'] = true;

        $this->validateObject($object);

        $jobInstanceId = $object->getId();

        $this->eventDispatcher->dispatch(new RemoveEvent($object, $jobInstanceId, $options), StorageEvents::PRE_REMOVE);

        $this->jobInstanceRepository->remove($object->getCode());
        $this->deleteRunningUser($object);

        $this->eventDispatcher->dispatch(new RemoveEvent($object, $jobInstanceId, $options), StorageEvents::POST_REMOVE);
    }

    /**
     * @param JobInstance[] $objects
     */
    public function removeAll(array $objects, array $options = []): void
    {
        if (empty($objects)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new RemoveEvent($objects, null), StorageEvents::PRE_REMOVE_ALL);

        foreach ($objects as $object) {
            $this->validateObject($object);

            $this->eventDispatcher->dispatch(new RemoveEvent($object, $object->getId(), $options), StorageEvents::PRE_REMOVE);
        }

        $removedObjects = [];
        foreach ($objects as $object) {
            $removedObjects[$object->getId()] = $object;

            $this->jobInstanceRepository->remove($object->getCode());
        }

        foreach ($removedObjects as $id => $object) {
            $this->eventDispatcher->dispatch(new RemoveEvent($object, $id, $options), StorageEvents::POST_REMOVE);
        }

        $this->eventDispatcher->dispatch(
            new RemoveEvent($objects, array_keys($removedObjects)),
            StorageEvents::POST_REMOVE_ALL
        );
    }

    private function deleteRunningUser(JobInstance $jobInstance)
    {
        try {
            $this->deleteRunningUser->execute($jobInstance->getCode());
        } catch (\Exception $e) {
            $this->logger->warning('Error occurred trying to remove running user.', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function validateObject(mixed $object): void
    {
        if (!$object instanceof JobInstance) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    JobInstance::class,
                    ClassUtils::getClass($object)
                )
            );
        }
    }
}
