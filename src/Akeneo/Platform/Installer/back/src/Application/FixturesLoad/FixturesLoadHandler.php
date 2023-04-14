<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\FixturesLoad;

use Akeneo\Platform\Installer\Application\DatabaseInstall\DatabaseInstallCommand;
use Akeneo\Platform\Installer\Domain\FixtureLoader\JobInstanceBuilderInterface;
use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\AkeneoBatchJobInterface;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvent;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class FixturesLoadHandler
{
    public function __construct(
        private readonly AkeneoBatchJobInterface $akeneoBatchJob,
        private readonly JobInstanceBuilderInterface $jobInstanceBuilder,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function handle(FixtureLoadCommand $command): void
    {
        $io = $command->getIo();

        $io->title('Load fixture');

        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, [
                'catalog' => $command->getOption('catalog'),
            ]),
            InstallerEvents::PRE_LOAD_FIXTURES
        );

        $this->loadFixturesStep($io, $command->getOptions());

        $this->eventDispatcher->dispatch(
            new InstallerEvent( null, [
                'catalog' => $command->getOption('catalog'),
            ]),
            InstallerEvents::POST_LOAD_FIXTURES
        );
    }

    private function loadFixturesStep(SymfonyStyle $io, array $options): void
    {
        $io->info(sprintf('Load jobs for fixtures. (data set: %s)', $options['catalog']));

        $jobInstances = $this->jobInstanceBuilder->build();
        $configuredJobInstances = $this->configureJobInstances($catalogPath, $jobInstances, $replacePaths);
        $this->jobInstanceSaver->saveAll($configuredJobInstances, ['is_installation' => true]);
    }
}
