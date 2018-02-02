<?php

declare(strict_types=1);

namespace Pim\Component\Limit\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Limit\Registry\CurrentNumberRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LimitAttributeSubscriber implements EventSubscriberInterface
{
    /** @var CurrentNumberRegistry $registry */
    private $registry;

    /**
     * @param CurrentNumberRegistry     $registry
     */
    public function __construct(CurrentNumberRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE        => 'increment',
            StorageEvents::POST_SAVE_ALL    => 'incrementAll',
            StorageEvents::POST_REMOVE      => 'decrement',
            StorageEvents::POST_REMOVE_ALL  => 'decrementAll',
        ];
    }

    /**
     * @param GenericEvent $event
     * @throws \Doctrine\DBAL\DBALException
     */
    public function increment(GenericEvent $event) : void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof Attribute) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if ($event->hasArgument('is_new') && true == $event->getArgument('is_new')) {
            $this->registry->incrementAttributeNumber(1, 'ATTRIBUTE_NUMBER');

            if ($attribute->isLocalizable() && $attribute->isScopable()) {
                $this->registry->incrementAttributeNumber(1, 'ATTRIBUTE_LOCALIZABLE_AND_SCOPABLE_NUMBER');
            } elseif ($attribute->isScopable()) {
                $this->registry->incrementAttributeNumber(1, 'ATTRIBUTE_ONLY_SCOPABLE_NUMBER');
            } elseif ($attribute->isLocalizable()) {
                $this->registry->incrementAttributeNumber(1, 'ATTRIBUTE_ONLY_LOCALIZABLE_NUMBER');
            }
        }
    }

    /**
     * @param GenericEvent $event
     * @throws \Doctrine\DBAL\DBALException
     */
    public function incrementAll(GenericEvent $event) : void
    {
        $attributes = $event->getSubject();
        if (!current($attributes) instanceof Attribute) {
            return;
        }

        if ($event->hasArgument('are_new')) {
            $objectsAreNew = $event->getArgument('are_new');

            $this->registry->incrementAttributeNumber(
                count(array_filter($attributes, function ($attribute) use ($objectsAreNew) {
                    return $objectsAreNew[$attribute->getId()] ?? false;
                })),
                'ATTRIBUTE_NUMBER'
            );
            $this->registry->incrementAttributeNumber(
                count(array_filter($attributes, function ($attribute) use ($objectsAreNew) {
                    return ($objectsAreNew[$attribute->getId()] ?? false)
                        && $attribute->isLocalizable() && $attribute->isScopable();
                })),
                'ATTRIBUTE_LOCALIZABLE_AND_SCOPABLE_NUMBER'
            );
            $this->registry->incrementAttributeNumber(
                count(array_filter($attributes, function ($attribute) use ($objectsAreNew) {
                    return ($objectsAreNew[$attribute->getId()] ?? false)
                        && false == $attribute->isLocalizable() && $attribute->isScopable();
                })),
                'ATTRIBUTE_ONLY_SCOPABLE_NUMBER'
            );
            $this->registry->incrementAttributeNumber(
                count(array_filter($attributes, function ($attribute) use ($objectsAreNew) {
                    return ($objectsAreNew[$attribute->getId()] ?? false)
                        && $attribute->isLocalizable() && false == $attribute->isScopable();
                })),
                'ATTRIBUTE_ONLY_LOCALIZABLE_NUMBER'
            );
        }
    }

    /**
     * @param GenericEvent $event
     * @throws \Doctrine\DBAL\DBALException
     */
    public function decrement(GenericEvent $event) : void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof Attribute) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->registry->decrementAttributeNumber(1, 'ATTRIBUTE_NUMBER');
    }

    /**
     * @param GenericEvent $event
     * @throws \Doctrine\DBAL\DBALException
     */
    public function decrementAll(GenericEvent $event) : void
    {
        $attributes = $event->getSubject();
        if (!current($attributes) instanceof Attribute) {
            return;
        }

        $this->registry->incrementAttributeNumber(count($attributes), 'ATTRIBUTE_NUMBER');
    }
}
