<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\Installer\AssetsInstaller;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\Installer\FixturesInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This commands reset the database fixtures for the reference entity.
 * It also is an event listener used during the PIM isntallation.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerCommand extends Command implements EventSubscriberInterface
{
    public const ICECAT_DEMO_DEV = 'icecat_demo_dev';
    protected static $defaultName = self::RESET_FIXTURES_COMMAND_NAME;

    private const RESET_FIXTURES_COMMAND_NAME = 'akeneo:reference-entity:reset-fixtures';

    /** @var FixturesInstaller */
    private $fixturesInstaller;

    /** @var AssetsInstaller */
    private $assetInstaller;

    /** @var bool */
    private $shouldLoadReferenceEntitiesFixtures;

    public function __construct(
        FixturesInstaller $fixturesInstaller,
        AssetsInstaller $assetInstaller,
        bool $shouldLoadReferenceEntitiesFixtures
    ) {
        parent::__construct(self::RESET_FIXTURES_COMMAND_NAME);

        $this->fixturesInstaller = $fixturesInstaller;
        $this->assetInstaller = $assetInstaller;
        $this->shouldLoadReferenceEntitiesFixtures = $shouldLoadReferenceEntitiesFixtures;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Resets the fixtures of the reference entity bounded context.')
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

    public function loadFixtures(InstallerEvent $event): void
    {
        if (
            $this->shouldLoadReferenceEntitiesFixtures ||
            substr(
                $event->getArgument('catalog'),
                -strlen(self::ICECAT_DEMO_DEV)
            ) === self::ICECAT_DEMO_DEV
        ) {
            $this->fixturesInstaller->loadCatalog();
        }
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
        $this->fixturesInstaller->loadCatalog();

        return 0;
    }
}
