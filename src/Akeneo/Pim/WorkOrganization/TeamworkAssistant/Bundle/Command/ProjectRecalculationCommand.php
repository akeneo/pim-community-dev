<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Command;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run project calculations for all enrichment projects.
 * Use this command to run the project calculation by cron task.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectRecalculationCommand extends Command
{
    protected static $defaultName = 'pimee:project:recalculate';

    /** @var CommandExecutor */
    protected $commandExecutor;

    /** @var ObjectDetacherInterface */
    private $objectDetacher;

    /** @var ProjectRepositoryInterface */
    private $projectRepository;

    /** @var string */
    private $projectCalculationJobName;

    public function __construct(
        ObjectDetacherInterface $objectDetacher,
        ProjectRepositoryInterface $projectRepository,
        string $projectCalculationJobName
    ) {
        parent::__construct();

        $this->objectDetacher = $objectDetacher;
        $this->projectRepository = $projectRepository;
        $this->projectCalculationJobName = $projectCalculationJobName;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Recalculate all enrichment projects (Warning: Be aware it can be very time-consuming)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor = new CommandExecutor(
            $input,
            $output,
            $this->getApplication()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projects = $this->projectRepository->findAll();

        $projectToDetach = null;
        foreach ($projects as $project) {
            $this->commandExecutor->runCommand('akeneo:batch:job', [
                'code' => $this->projectCalculationJobName,
                '-c'   => sprintf('{"project_code":"%s"}', $project->getCode()),
            ]);

            if (null !== $projectToDetach) {
                $this->objectDetacher->detach($projectToDetach);
            }

            $projectToDetach = $project;
        }

        return 0;
    }
}
