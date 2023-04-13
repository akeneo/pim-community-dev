<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Command;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\InstallData;
use Akeneo\Platform\Installer\Application\DatabaseInstall\DatabaseInstallCommand;
use Akeneo\Platform\Installer\Application\DatabaseInstall\DatabaseInstallHandler;
use Akeneo\Platform\Installer\Application\ResetElasticsearchIndexes\ResetElasticSearchIndexesCommand;
use Akeneo\Platform\Installer\Application\ResetElasticsearchIndexes\ResetElasticSearchIndexesHandler;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Tool\Component\Console\CommandExecutor;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DatabaseInstallerCommand extends Command
{
    public static $defaultName = 'pim:installer:db-2';

    const LOAD_ALL = 'all';
    const LOAD_BASE = 'base';

    public function __construct(
        private readonly DatabaseInstallHandler $databaseInstallHandler,
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
        $this->databaseInstallHandler->handle(new DatabaseInstallCommand(
            new SymfonyStyle($input, $output),
            $input->getOptions()
        ));

        /**
        $io->title('Prepare database schema');

        try {
            $this->commandExecutor
                ->runCommand(
                    'doctrine:schema:update',
                    ['--force' => true, '--no-interaction' => true]
                );
        } catch (\Exception $e) {
            $io->error([
                'Trying to install PIM on an existing database is impossible.',
                $e->getMessage()
            ]);

            return Command::FAILURE;
        }

        if (false === $input->getOption('withoutIndexes')) {
            $this->resetElasticSearchIndexesHandler->handle(new ResetElasticSearchIndexesCommand($io));
        }

        $entityManager = $this->entityManager;
        $entityManager->clear();

        $this->eventDispatcher->dispatch(
            new InstallerEvent($this->commandExecutor, null, [
                'catalog' => $input->getOption('catalog'),
            ]),
            InstallerEvents::POST_DB_CREATE
        );

        $this->setLatestKnownMigration($input);

        if (false === $input->getOption('withoutFixtures')) {
            $this->eventDispatcher->dispatch(
                new InstallerEvent($this->commandExecutor, null, [
                    'catalog' => $input->getOption('catalog'),
                ]),
                InstallerEvents::PRE_LOAD_FIXTURES
            );

            $this->loadFixturesStep($input, $output);

            $this->eventDispatcher->dispatch(
                new InstallerEvent($this->commandExecutor, null, [
                    'catalog' => $input->getOption('catalog'),
                ]),
                InstallerEvents::POST_LOAD_FIXTURES
            );
        }

        $this->installTimeQuery->withDatetime(new \DateTimeImmutable());
        **/

        return Command::SUCCESS;
    }
}
