<?php

namespace Oro\Bundle\CronBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\CronBundle\Entity\Schedule;

class CronCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('oro:cron')
            ->setDescription('Cron commands launcher');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commands   = $this->getApplication()->all('oro:cron');
        $em         = $this->getContainer()->get('doctrine.orm.entity_manager');
        $dbCommands = $em->getRepository('OroCronBundle:Schedule')->findAll();

        foreach ($commands as $name => $command) {
            $output->writeln('');
            $output->writeln(sprintf('Processing command "<info>%s</info>"', $name));

            $dbCommand = array_filter(
                $dbCommands,
                function ($element) use ($name) {
                    return $element->getCommand() == $name;
                }
            );

            if (empty($dbCommand)) {
                $output->writeln(sprintf('<comment>New command found: <info>%s</info>. Setting up schedule..</comment>', $name));

                $schedule = new Schedule();

                $schedule->setCommand($name);

                $em->persist($schedule);

                continue;
            }

            $dbCommand = current($dbCommand);

            if (!$dbCommand->getDefinition()) {
                $output->writeln(sprintf('<comment>No schedule for <info>%s</info></comment>', $name));

                continue;
            }

            $cron = \Cron\CronExpression::factory('*/5 * * * * *');

            if ($cron->isDue()) {
                $job = new Job($name);

                $em->persist($job);

                $output->writeln(sprintf('Command "<info>%s</info>" added to job queue', $name));
            }
        }

        $em->flush();

        $output->writeln('');
        $output->writeln('All commands finished');
    }
}