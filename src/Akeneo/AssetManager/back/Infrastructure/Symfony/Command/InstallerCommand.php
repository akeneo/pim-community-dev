<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command;

use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\AssetsInstaller;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesInstaller;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This commands reset the database fixtures for the asset family.
 * It also is an event listener used during the PIM isntallation.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerCommand extends ContainerAwareCommand implements EventSubscriberInterface
{
    private const RESET_FIXTURES_COMMAND_NAME = 'akeneo:asset-manager:reset-fixtures';

    /** @var FixturesInstaller */
    private $fixturesInstaller;

    /** @var AssetsInstaller */
    private $assetInstaller;

    /** @var string */
    private $catalogName;

    public function __construct(
        FixturesInstaller $fixturesInstaller,
        AssetsInstaller $assetInstaller,
        string $catalogName
    ) {
        parent::__construct(self::RESET_FIXTURES_COMMAND_NAME);

        $this->fixturesInstaller = $fixturesInstaller;
        $this->assetInstaller = $assetInstaller;
        $this->catalogName = $catalogName;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::RESET_FIXTURES_COMMAND_NAME)
            ->setDescription('Resets the fixtures of the asset family bounded context.')
            ->setHidden(true);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_SYMFONY_ASSETS_DUMP => ['installAssets'],
            InstallerEvents::POST_ASSETS_DUMP         => ['installAssets'],
            InstallerEvents::POST_DB_CREATE           => ['createSchema'],
            InstallerEvents::POST_LOAD_FIXTURES       => ['loadFixtures'],
        ];
    }

    public function createSchema(): void
    {
        $this->fixturesInstaller->createSchema();
    }

    public function loadFixtures(): void
    {
        $this->fixturesInstaller->loadCatalog($this->catalogName);
    }

    public function installAssets(GenericEvent $event): void
    {
        $shouldSymlink = $event->getArgument('symlink');
        $this->assetInstaller->installAssets($shouldSymlink);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fixturesInstaller->createSchema();
        $this->fixturesInstaller->loadCatalog(FixturesInstaller::ICE_CAT_DEMO_DEV_CATALOG);
    }
}
