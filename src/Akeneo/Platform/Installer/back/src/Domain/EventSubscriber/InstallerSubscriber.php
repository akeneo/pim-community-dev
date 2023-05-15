<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Domain\EventSubscriber;

use Akeneo\Platform\Installer\Domain\Event\InstallerEvent;
use Akeneo\Platform\Installer\Domain\Event\InstallerEvents;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstallerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SymfonyStyle $io
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_COMMAND_EXECUTED => 'output',
        ];
    }

    public function output(InstallerEvent $event): void
    {
        if (!$event->hasArgument('output')) {
            return;
        }

        $this->io->block($event->getArgument('output'));
    }
}
