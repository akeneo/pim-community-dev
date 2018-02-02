<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Limit\Registry\QuotaRegistry;
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

    /** @var QuotaRegistry */
    protected $quotaRegistry;

    /**
     * @param ObjectManager                  $objectManager
     * @param EventDispatcherInterface       $eventDispatcher
     * @param QuotaRegistry                  $quotaRegistry
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        QuotaRegistry $quotaRegistry
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->quotaRegistry = $quotaRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function save($attribute, array $options = [])
    {
        $this->validateAttribute($attribute);

        $options['unitary'] = true;
        $options['is_new'] = null === $attribute->getId();

        if ($this->quotaRegistry->isLimitReachedForAttribute(1) && $options['is_new']) {
            throw new \Exception();
        }

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($attribute, $options));

        $this->objectManager->persist($attribute);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($attribute, $options));
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
        $areObjectsNew = array_map(function ($attribute) {
            return null === $attribute->getId();
        }, $attributes);

        if ($this->quotaRegistry->isLimitReachedForAttribute(count(array_filter($areObjectsNew)))) {
            throw new \Exception('BLA');
        }

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($attributes, $options));

        foreach ($attributes as $i => $attribute) {
            $this->validateAttribute($attribute);

            $this->eventDispatcher->dispatch(
                StorageEvents::PRE_SAVE,
                new GenericEvent(
                    $attribute,
                    array_merge($options, ['is_new' => $areObjectsNew[$i]])
                )
            );

            $this->objectManager->persist($attribute);
        }

        $this->objectManager->flush();

        foreach ($attributes as $i => $attribute) {
            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE,
                new GenericEvent(
                    $attribute,
                    array_merge($options, ['is_new' => $areObjectsNew[$i]])
                )
            );
        }

        $this->eventDispatcher->dispatch(
            StorageEvents::POST_SAVE_ALL,
            new GenericEvent($attributes, array_merge($options, ['are_new' => $areObjectsNew]))
        );
    }

    /**
     * @param $attribute
     */
    protected function validateAttribute($attribute)
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\AttributeInterface", "%s" provided.',
                    ClassUtils::getClass($attribute)
                )
            );
        }
    }
}
