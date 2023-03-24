<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Fakes;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CleanCategoryDataAfterChannelChangeSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [];
    }

    public function cleanCategoryDataForChannel(GenericEvent $event): void
    {
    }

    public function cleanCategoryDataForChannelLocale(GenericEvent $event): void
    {
    }
}
