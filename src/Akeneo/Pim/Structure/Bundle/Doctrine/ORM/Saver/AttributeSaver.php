<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Attribute saver
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager                  $objectManager
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($attribute, array $options = [])
    {
        $this->validateAttribute($attribute);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(new GenericEvent($attribute, $options), StorageEvents::PRE_SAVE);

        $this->objectManager->persist($attribute);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(new GenericEvent($attribute, $options), StorageEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $attributes, array $options = [])
    {
        if (empty($attributes)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new GenericEvent($attributes, $options), StorageEvents::PRE_SAVE_ALL);

        foreach ($attributes as $attribute) {
            $this->validateAttribute($attribute);

            $this->eventDispatcher->dispatch(new GenericEvent($attribute, $options), StorageEvents::PRE_SAVE);

            $this->objectManager->persist($attribute);
        }

        $this->objectManager->flush();

        foreach ($attributes as $attribute) {
            $this->eventDispatcher->dispatch(new GenericEvent($attribute, $options), StorageEvents::POST_SAVE);
        }

        $this->eventDispatcher->dispatch(new GenericEvent($attributes, $options), StorageEvents::POST_SAVE_ALL);
    }

    protected function validateAttribute($attribute)
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Pim\Structure\Component\Model\AttributeInterface", "%s" provided.',
                    ClassUtils::getClass($attribute)
                )
            );
        }
    }
}
