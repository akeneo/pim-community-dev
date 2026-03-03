<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Exception\DuplicateObjectException;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Base saver, declared as different services for different classes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSaver implements SaverInterface, BulkSaverInterface
{
    public function __construct(
        private ObjectManager $objectManager,
        private EventDispatcherInterface $eventDispatcher,
        private string $savedClass
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = []): void
    {
        $this->validateObject($object);

        $options['unitary'] = true;
        $options['is_new'] = null === $object->getId();

        $this->eventDispatcher->dispatch(new GenericEvent($object, $options), StorageEvents::PRE_SAVE);

        try {
            $this->objectManager->persist($object);
            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new DuplicateObjectException($e->getMessage());
        }

        $this->eventDispatcher->dispatch(new GenericEvent($object, $options), StorageEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = []): void
    {
        if (empty($objects)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new GenericEvent($objects, $options), StorageEvents::PRE_SAVE_ALL);

        $areObjectsNew = array_map(function ($object) {
            return null === $object->getId();
        }, $objects);

        foreach ($objects as $i => $object) {
            $this->validateObject($object);

            $this->eventDispatcher->dispatch(
                new GenericEvent($object, array_merge($options, ['is_new' => $areObjectsNew[$i]])),
                StorageEvents::PRE_SAVE
            );

            $this->objectManager->persist($object);
        }

        $this->objectManager->flush();

        foreach ($objects as $i => $object) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($object, array_merge($options, ['is_new' => $areObjectsNew[$i]])),
                StorageEvents::POST_SAVE
            );
        }

        $this->eventDispatcher->dispatch(new GenericEvent($objects, $options), StorageEvents::POST_SAVE_ALL);
    }

    private function validateObject(object $object): void
    {
        if (!$object instanceof $this->savedClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    $this->savedClass,
                    ClassUtils::getClass($object)
                )
            );
        }
    }
}
