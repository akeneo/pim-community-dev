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
use Psr\Log\LoggerInterface;
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
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(FixtureLoadCommand $command): void
    {
        $this->logger->info(sprintf('Load jobs for fixtures. (data set: %s)', $command->catalog));

        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, [
                'catalog' => $command->catalog,
            ]),
            InstallerEvents::PRE_LOAD_FIXTURES,
        );

        $jobDefinitions = $this->createJobInstances($command->catalog);

        $this->loadFixtures($jobDefinitions, $command->catalog);

        $this->cleanJobInstances();

        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, [
                'catalog' => $command->catalog,
            ]),
            InstallerEvents::POST_LOAD_FIXTURES,
        );
    }

    /**
     * @return mixed[]
     */
    private function createJobInstances(string $catalog): array
    {
        $jobDefinitions = $this->getJobDefinition->get($this->jobsFilePaths);
        foreach ($jobDefinitions as $jobDefinition) {
            $jobDefinition['configuration']['storage']['file_path'] = sprintf('%s/%s', $catalog, $jobDefinition['configuration']['storage']['file_path']);
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
     */
    private function loadFixtures(array $jobDefinitions, string $catalog): void
    {
        foreach ($jobDefinitions as $jobDefinition) {
            $this->eventDispatcher->dispatch(
                new InstallerEvent($jobDefinition['code'], [
                    'catalog' => $catalog,
                ]),
                InstallerEvents::PRE_LOAD_FIXTURE,
            );

            $this->logger->info(
                sprintf('Please wait, the "%s" are processing...', $jobDefinition['code'])
            );

            $this->launchJob->execute([
                'code' => $jobDefinition['code'],
                '--no-debug' => true,
                '--no-log' => true,
                '-v' => true,
            ], true);

            $this->eventDispatcher->dispatch(
                new InstallerEvent($jobDefinition['code'], [
                    'job_name' => $jobDefinition['alias'],
                    'catalog' => $catalog,
                ]),
                InstallerEvents::POST_LOAD_FIXTURE,
            );
        }
    }

    private function cleanJobInstances(): void
    {
        $this->logger->info('Delete jobs for fixtures');
        $this->removeJobInstance->remove();
    }
}
