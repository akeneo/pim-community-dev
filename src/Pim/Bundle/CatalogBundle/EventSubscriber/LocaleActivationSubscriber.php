<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\LocaleEvents;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleActivationSubscriber implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param EventDispatcherInterface  $eventDispatcher
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, LocaleRepositoryInterface $localeRepository)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => 'localeStatusDispatcher'];
    }

    /**
     * @param GenericEvent $event
     */
    public function localeStatusDispatcher(GenericEvent $event)
    {
        $locale = $event->getSubject();

        if (!$locale instanceof LocaleInterface) {
            return;
        }

        $activatedLocaleCodes = $this->localeRepository->getActivatedLocaleCodes();

        if (0 === $locale->getChannels()->count() && in_array($locale->getCode(), $activatedLocaleCodes)) {
            $this->eventDispatcher->dispatch(LocaleEvents::LOCALE_DEACTIVATED, new GenericEvent($locale));
        }

        if (0 < $locale->getChannels()->count() && !in_array($locale->getCode(), $activatedLocaleCodes)) {
            $this->eventDispatcher->dispatch(LocaleEvents::LOCALE_ACTIVATED, new GenericEvent($locale));
        }
    }
}
