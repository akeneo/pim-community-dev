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

use Pim\Bundle\InstallerBundle\CommandExecutor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run project calculations for all enrichment projects.
 * Use this command to run the project calculation by cron task.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectRecalculationCommand extends ContainerAwareCommand
{
    /** @var CommandExecutor */
    protected $commandExecutor;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pimee:project:recalculate')
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
        $objectDetacher = $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher');
        $jobName = $this->getContainer()->getParameter('pimee_teamwork_assistant.project_calculation.job_name');

        $projects = $this->getContainer()
            ->get('pimee_teamwork_assistant.repository.project')
            ->findAll();

        $projectToDetach = null;
        foreach ($projects as $project) {
            $this->commandExecutor->runCommand('akeneo:batch:job', [
                'code' => $jobName,
                '-c'   => sprintf('{"project_code":"%s"}', $project->getCode()),
            ]);

            if (null !== $projectToDetach) {
                $objectDetacher->detach($projectToDetach);
            }

            $projectToDetach = $project;
        }
    }
}
