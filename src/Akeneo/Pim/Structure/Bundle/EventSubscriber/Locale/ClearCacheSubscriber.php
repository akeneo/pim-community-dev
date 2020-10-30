<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber\Locale;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Validates and saves the family variants belonging to a family whenever it is updated.
 *
 * @author    jmleroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClearCacheSubscriber implements EventSubscriberInterface
{
    private ChannelExistsWithLocaleInterface $cachedChannelExistsWithLocale;

    public function __construct(ChannelExistsWithLocaleInterface $cachedChannelExistsWithLocale)
    {
        $this->cachedChannelExistsWithLocale = $cachedChannelExistsWithLocale;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'clearCache',
            StorageEvents::POST_SAVE_ALL => 'clearCache',
        ];
    }

    /**
     * Clear Locale cache
     */
    public function clearCache(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof LocaleInterface) {
            return;
        }

        $this->cachedChannelExistsWithLocale->clearCache();
    }
}
