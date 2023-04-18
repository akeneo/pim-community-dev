<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Command;

use Akeneo\Platform\Installer\Application\DatabaseInstall\DatabaseInstallCommand;
use Akeneo\Platform\Installer\Application\DatabaseInstall\DatabaseInstallHandler;
use Akeneo\Platform\Installer\Application\FixturesLoad\FixtureLoadCommand;
use Akeneo\Platform\Installer\Application\FixturesLoad\FixturesLoadHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DatabaseInstallerCommand extends Command
{
    public static $defaultName = 'pim:installer:db-2';

    const LOAD_ALL = 'all';
    const LOAD_BASE = 'base';

    public function __construct(
        private readonly DatabaseInstallHandler $databaseInstallHandler,
        private readonly FixturesLoadHandler $fixturesLoadHandler
    )
    {
        parent::__construct(self::$defaultName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'fixtures',
                null,
                InputOption::VALUE_REQUIRED,
                'Determines fixtures to load (can be just OroPlatform or all)',
                self::LOAD_ALL
            )
            ->addOption(
                'withoutIndexes',
                null,
                InputOption::VALUE_OPTIONAL,
                'Should the command setup the elastic search indexes',
                false
            )
            ->addOption(
                'withoutFixtures',
                null,
                InputOption::VALUE_OPTIONAL,
                'Should the command install any fixtures',
                false
            )
            ->addOption(
                'catalog',
                null,
                InputOption::VALUE_OPTIONAL,
                'Directory of the fixtures to install',
                'src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->databaseInstallHandler->handle(new DatabaseInstallCommand(
                $io,
                $input->getOptions()
            ));

            if ($input->getOption('withoutFixtures')) {
                return Command::SUCCESS;
            }

            $this->fixturesLoadHandler->handle(new FixtureLoadCommand($io, $input->getOptions()));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
