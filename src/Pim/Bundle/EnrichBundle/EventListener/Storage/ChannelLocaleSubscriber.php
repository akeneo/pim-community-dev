<?php

namespace Pim\Bundle\EnrichBundle\EventListener\Storage;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Storage event subscriber that update channel locales
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @todo      In 1.5 completeness manager become required
 */
class ChannelLocaleSubscriber implements EventSubscriberInterface
{
    /** @var LocaleRepositoryInterface */
    protected $repository;

    /** @var BulkSaverInterface */
    protected $saver;

    /** @var CompletenessGeneratorInterface */
    protected $completeness;

    /**
     * @param LocaleRepositoryInterface      $repository
     * @param BulkSaverInterface             $saver
     * @param CompletenessGeneratorInterface $completeness
     */
    public function __construct(
        LocaleRepositoryInterface $repository,
        BulkSaverInterface $saver,
        CompletenessGeneratorInterface $completeness = null
    ) {
        $this->repository   = $repository;
        $this->saver        = $saver;
        $this->completeness = $completeness;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeChannel',
            StorageEvents::PRE_SAVE   => 'updateChannel',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function removeChannel(GenericEvent $event)
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $locales = $channel->getLocales();
        $updatedLocales = [];

        foreach ($locales as $locale) {
            $locale->removeChannel($channel);
            $updatedLocales[] = $locale;
        }

        if (!empty($updatedLocales)) {
            $this->saver->saveAll($updatedLocales);
        }
    }

    /**
     * @param GenericEvent $event
     */
    public function updateChannel(GenericEvent $event)
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $oldLocales = $this->repository->getDeletedLocalesForChannel($channel);
        $newLocales = $channel->getLocales();
        $updatedLocales = [];

        foreach ($oldLocales as $locale) {
            $locale->removeChannel($channel);
            $updatedLocales[] = $locale;

            if (null !== $this->completeness) {
                $this->completeness->scheduleForChannelAndLocale($channel, $locale);
            }
        }

        foreach ($newLocales as $locale) {
            if (!$locale->hasChannel($channel)) {
                $locale->addChannel($channel);
                $updatedLocales[] = $locale;
            }
        }

        if (!empty($updatedLocales)) {
            $this->saver->saveAll($updatedLocales);
        }
    }
}
