<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\InstallData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateLastResetDateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly InstallData $installData,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_INSTANCE_RESET => 'onInstanceReset',
        ];
    }

    public function onInstanceReset(): void
    {
        $this->installData->withDatetime(new \DateTimeImmutable('now'));
    }
}
