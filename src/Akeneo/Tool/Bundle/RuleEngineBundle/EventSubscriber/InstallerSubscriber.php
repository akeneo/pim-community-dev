<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This commands reset the database fixtures for the rules
 * It also is an event listener used during the PIM installation.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerSubscriber implements EventSubscriberInterface
{
    /** @var RulesInstaller */
    private $rulesInstaller;

    public function __construct(RulesInstaller $rulesInstaller)
    {
        $this->rulesInstaller = $rulesInstaller;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_SYMFONY_ASSETS_DUMP => ['installRules'],
            InstallerEvents::POST_ASSETS_DUMP         => ['installRules']
        ];
    }

    public function installRules(GenericEvent $event): void
    {
        $shouldSymlink = $event->getArgument('symlink');
        $this->rulesInstaller->installRules($shouldSymlink);
    }
}
