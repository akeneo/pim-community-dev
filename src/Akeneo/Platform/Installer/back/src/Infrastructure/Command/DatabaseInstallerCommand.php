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
use Akeneo\Platform\Installer\Domain\EventSubscriber\InstallerSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DatabaseInstallerCommand extends Command
{
    public static $defaultName = 'pim:installer:db';

    public function __construct(
        private readonly DatabaseInstallHandler $databaseInstallHandler,
        private readonly FixturesLoadHandler $fixturesLoadHandler,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct(self::$defaultName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'withoutIndexes',
                null,
                InputOption::VALUE_OPTIONAL,
                'Should the command setup the elastic search indexes',
                false,
            )
            ->addOption(
                'withoutFixtures',
                null,
                InputOption::VALUE_OPTIONAL,
                'Should the command install any fixtures',
                false,
            )
            ->addOption(
                'catalog',
                null,
                InputOption::VALUE_OPTIONAL,
                'Directory of the fixtures to install',
                'src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->eventDispatcher->addSubscriber(new InstallerSubscriber($io));

        try {
            $this->databaseInstallHandler->handle(new DatabaseInstallCommand(
                !$input->getOption('withoutIndexes'),
                $input->getOption('env'),
            ));

            if (false === $input->getOption('withoutFixtures')) {
                $this->fixturesLoadHandler->handle(new FixtureLoadCommand(
                    $input->getOption('catalog'),
                ));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            return Command::FAILURE;
        }
    }
}
