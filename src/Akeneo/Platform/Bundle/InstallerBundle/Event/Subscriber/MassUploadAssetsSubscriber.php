<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber;

use Akeneo\Asset\Bundle\Command\CopyAssetFilesCommand;
use Akeneo\Asset\Bundle\Command\ProcessMassUploadCommand;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixturePathProvider;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
     * @param InstallerEvent $event
     *
     * @throws \Exception
     */
    public function massUploadAssets(InstallerEvent $event)
    {
        if ('fixtures_asset_csv' !== $event->getSubject()) {
            return;
        }

        $assetsPath = $this->pathProvider->getFixturesPath($event->getArgument('catalog')) . 'assets';
        if (!$this->filesystem->exists($assetsPath)) {
            return;
        }

        $commandExecutor = $event->getCommandExecutor();
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
