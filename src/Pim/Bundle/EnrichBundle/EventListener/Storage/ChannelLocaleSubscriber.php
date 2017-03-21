<?php

namespace Pim\Bundle\EnrichBundle\EventListener\Storage;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Storage event subscriber that update channel locales
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        CompletenessGeneratorInterface $completeness
    ) {
        $this->repository = $repository;
        $this->saver = $saver;
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
            // TODO TIP-694: disabling completeness calculation
            // $this->completeness->scheduleForChannelAndLocale($channel, $locale);
        }

        foreach ($newLocales as $locale) {
            if (!$locale->hasChannel($channel)) {
                $locale->addChannel($channel);
            }
            $updatedLocales[] = $locale;
        }

        if (!empty($updatedLocales)) {
            $this->saver->saveAll($updatedLocales);
        }
    }
}
