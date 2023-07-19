<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\SaveResetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateLastResetDateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SaveResetEvent $saveResetEvent,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_RESET_INSTANCE => 'onInstanceReset',
        ];
    }

    public function onInstanceReset(): void
    {
        $this->saveResetEvent->withDatetime(new \DateTimeImmutable('now'));
    }
}
