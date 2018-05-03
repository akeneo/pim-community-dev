<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\InstallerBundle\Event\Subscriber;

use Pim\Bundle\InstallerBundle\CommandExecutor;
use Pim\Bundle\InstallerBundle\Event\InstallerEvents;
use Pim\Bundle\InstallerBundle\FixtureLoader\FixturePathProvider;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class MassUploadAssetsSubscriber implements EventSubscriberInterface
{
    const COPY_FILES_COMMAND  = 'pim:product-asset:copy-asset-files';
    const MASS_UPLOAD_COMMAND = 'pim:product-asset:mass-upload';

    /** @var FixturePathProvider */
    protected $pathProvider;

    /**
     * @param FixturePathProvider $pathProvider
     */
    public function __construct(FixturePathProvider $pathProvider)
    {
        $this->pathProvider = $pathProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURE => 'massUploadAssets',
        ];
    }

    /***
     * @param GenericEvent $event
     *
     * @throws \Exception
     */
    public function massUploadAssets(GenericEvent $event)
    {
        if ('fixtures_asset_csv' !== $event->getSubject()) {
            return;
        }

        if (!$event->hasArgument('command_executor')) {
            return;
        }

        /** @var CommandExecutor $commandExecutor */
        $commandExecutor = $event->getArgument('command_executor');

        $assetsPath = $assetsPath = $this->pathProvider->getFixturesPath() . 'assets';
        $commandExecutor->runCommand(
            static::COPY_FILES_COMMAND,
            [
                '--from' => $assetsPath,
                '--user' => UserInterface::SYSTEM_USER_NAME,
                '--quiet' => true,
            ]
        );
        $commandExecutor->runCommand(
            static::MASS_UPLOAD_COMMAND,
            [
                '--user' => UserInterface::SYSTEM_USER_NAME,
                '--quiet' => true,
            ]
        );
    }
}
