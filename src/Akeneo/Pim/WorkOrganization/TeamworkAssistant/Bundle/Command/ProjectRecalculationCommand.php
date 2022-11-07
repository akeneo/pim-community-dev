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

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Job\ProjectsRecalculationLauncher;
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
    protected static $defaultDescription = 'Recalculate all enrichment projects (Warning: Be aware it can be very time-consuming)';

    public function __construct(private ProjectsRecalculationLauncher $projectsRecalculation)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->projectsRecalculation->launch();

        return Command::SUCCESS;
    }
}
