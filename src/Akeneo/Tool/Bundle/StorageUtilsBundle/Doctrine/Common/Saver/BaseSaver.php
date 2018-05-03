<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Base saver, declared as different services for different classes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $savedClass;

    /**
     * @param ObjectManager                  $objectManager
     * @param EventDispatcherInterface       $eventDispatcher
     * @param string                         $savedClass
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $savedClass
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->savedClass = $savedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        $this->validateObject($object);

        $options['unitary'] = true;
        $options['is_new'] = null === $object->getId();

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($object, $options));

        $this->objectManager->persist($object);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($object, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        if (empty($objects)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($objects, $options));

        $areObjectsNew = array_map(function ($object) {
            return null === $object->getId();
        }, $objects);

        foreach ($objects as $i => $object) {
            $this->validateObject($object);

            $this->eventDispatcher->dispatch(
                StorageEvents::PRE_SAVE,
                new GenericEvent($object, array_merge($options, ['is_new' => $areObjectsNew[$i]]))
            );

            $this->objectManager->persist($object);
        }

        $this->objectManager->flush();

        foreach ($objects as $i => $object) {
            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE,
                new GenericEvent($object, array_merge($options, ['is_new' => $areObjectsNew[$i]]))
            );
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($objects, $options));
    }

    /**
     * @param $object
     */
    protected function validateObject($object)
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
