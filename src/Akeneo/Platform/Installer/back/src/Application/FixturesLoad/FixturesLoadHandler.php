<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\FixturesLoad;

use Akeneo\Platform\Installer\Domain\CommandExecutor\LaunchJobInterface;
use Akeneo\Platform\Installer\Domain\Event\InstallerEvent;
use Akeneo\Platform\Installer\Domain\Event\InstallerEvents;
use Akeneo\Platform\Installer\Domain\Query\Sql\RemoveJobInstanceInterface;
use Akeneo\Platform\Installer\Domain\Query\Yaml\GetJobDefinitionInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceHandlerInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\File;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceHandlerInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class FixturesLoadHandler
{
    /**
     * @param string[] $jobsFilePaths
     */
    public function __construct(
        private readonly GetJobDefinitionInterface $getJobDefinition,
        private readonly RemoveJobInstanceInterface $removeJobInstance,
        private readonly CreateJobInstanceHandlerInterface $createJobInstanceHandler,
        private readonly LaunchJobInterface $launchJob,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly array $jobsFilePaths,
    ) {
    }

    public function handle(FixtureLoadCommand $command): void
    {
        $io = $command->getIo();

        $io->title('Load fixture');

        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, [
                'catalog' => $command->getOption('catalog'),
            ]),
            InstallerEvents::PRE_LOAD_FIXTURES,
        );

        $jobDefinitions = $this->createJobInstances($io, $command->getOptions());

        $this->loadFixtures($jobDefinitions, $io, $command->getOptions());

        $this->cleanJobInstances($io);

        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, [
                'catalog' => $command->getOption('catalog'),
            ]),
            InstallerEvents::POST_LOAD_FIXTURES,
        );
    }

    /**
     * @param string[] $options
     *
     * @return mixed[]
     */
    private function createJobInstances(SymfonyStyle $io, array $options): array
    {
        $io->info(sprintf('Load jobs for fixtures. (data set: %s)', $options['catalog']));

        $jobDefinitions = $this->getJobDefinition->get($this->jobsFilePaths);
        foreach ($jobDefinitions as $jobDefinition) {
            $jobDefinition['configuration']['storage']['file_path'] = sprintf('%s/%s', $options['catalog'], $jobDefinition['configuration']['storage']['file_path']);
            $this->createJobInstanceHandler->handle(new CreateJobInstanceCommand(
                $jobDefinition['type'],
                $jobDefinition['code'],
                $jobDefinition['label'],
                $jobDefinition['connector'],
                $jobDefinition['alias'],
                $jobDefinition['configuration'],
            ));
        }

        return $jobDefinitions;
    }

    /**
     * @param mixed[] $jobDefinitions
     * @param string[] $options
     */
    private function loadFixtures(array $jobDefinitions, SymfonyStyle $io, array $options): void
    {
        foreach ($jobDefinitions as $jobDefinition) {
            $this->eventDispatcher->dispatch(
                new InstallerEvent($jobDefinition['code'], [
                    'catalog' => $options['catalog'],
                ]),
                InstallerEvents::PRE_LOAD_FIXTURE,
            );

            $io->info(
                sprintf('Please wait, the "%s" are processing...', $jobDefinition['code']),
            );

            /** @var BufferedOutput $output */
            $output = $this->launchJob->execute([
                'code' => $jobDefinition['code'],
                '--no-debug' => true,
                '--no-log' => true,
                '-v' => true,
            ], true);

            $io->write($output->fetch());

            $this->eventDispatcher->dispatch(
                new InstallerEvent($jobDefinition['code'], [
                    'job_name' => $jobDefinition['alias'],
                    'catalog' => $options['catalog'],
                ]),
                InstallerEvents::POST_LOAD_FIXTURE,
            );
        }
    }

    private function cleanJobInstances(SymfonyStyle $io): void
    {
        $io->info('Start removing fixtures job instance');
        $this->removeJobInstance->remove();
    }
}
