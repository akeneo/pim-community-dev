<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Event\BulkSaveEvent;
use Akeneo\Component\StorageUtils\Event\SaveEvent;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new SaveEvent($attribute, $options));

        $this->objectManager->persist($attribute);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new SaveEvent($attribute, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $attributes, array $options = [])
    {
        if (empty($attributes)) {
            return;
        }

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new BulkSaveEvent($attributes, $options));

        foreach ($attributes as $attribute) {
            $this->validateAttribute($attribute);

            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new BulkSaveEvent($attribute, $options));

            $this->objectManager->persist($attribute);
        }

        $this->objectManager->flush();

        foreach ($attributes as $attribute) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new BulkSaveEvent($attribute, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new BulkSaveEvent($attributes, $options));
    }

    /**
     * @param $attribute
     */
    protected function validateAttribute($attribute)
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    AttributeInterface::class,
                    ClassUtils::getClass($attribute)
                )
            );
        }
    }
}
