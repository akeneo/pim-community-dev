<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\LocaleEvents;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => 'localeStatus'];
    }

    /**
     * @param GenericEvent $event
     */
    public function localeStatus(GenericEvent $event)
    {
        $locale = $event->getSubject();

        if (!$locale instanceof LocaleInterface) {
            return;
        }

        if (0 === $locale->getChannels()->count()) {
            $locale->setActivated(false);
            $this->eventDispatcher->dispatch(LocaleEvents::LOCALE_DEACTIVATED, new GenericEvent($locale));
        }

        if (0 < $locale->getChannels()->count()) {
            $locale->setActivated(true);
            $this->eventDispatcher->dispatch(LocaleEvents::LOCALE_ACTIVATED, new GenericEvent($locale));
        }
    }
}
