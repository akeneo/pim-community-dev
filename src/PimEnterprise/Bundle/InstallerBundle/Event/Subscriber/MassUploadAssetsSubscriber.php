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
use PimEnterprise\Bundle\ProductAssetBundle\Command\CopyAssetFilesCommand;
use PimEnterprise\Bundle\ProductAssetBundle\Command\ProcessMassUploadCommand;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class MassUploadAssetsSubscriber implements EventSubscriberInterface
{
    /** @var FixturePathProvider */
    protected $pathProvider;

    /** @var Filesystem */
    protected $filesystem;

    /**
     * @param FixturePathProvider $pathProvider
     * @param Filesystem $filesystem
     */
    public function __construct(FixturePathProvider $pathProvider, Filesystem $filesystem)
    {
        $this->pathProvider = $pathProvider;
        $this->filesystem = $filesystem;
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

        $assetsPath = $this->pathProvider->getFixturesPath() . 'assets';
        if (!$this->filesystem->exists($assetsPath)) {
            return;
        }

        if (
            !$event->hasArgument('command_executor') ||
            !$event->getArgument('command_executor') instanceof CommandExecutor
        ) {
            throw new \Exception(sprintf(
                '%s expects $event parameter to have a \'command_executor\' argument of type %s',
                __METHOD__,
                CommandExecutor::class
            ));
        }

        $commandExecutor = $event->getArgument('command_executor');
        $commandExecutor->runCommand(
            CopyAssetFilesCommand::NAME,
            [
                '--from' => $assetsPath,
                '--user' => UserInterface::SYSTEM_USER_NAME,
                '--quiet' => true,
            ]
        );
        $commandExecutor->runCommand(
            ProcessMassUploadCommand::NAME,
            [
                '--user' => UserInterface::SYSTEM_USER_NAME,
                '--quiet' => true,
            ]
        );
    }
}
