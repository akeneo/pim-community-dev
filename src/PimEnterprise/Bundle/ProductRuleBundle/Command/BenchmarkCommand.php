<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Command;

use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to benchmark the rule system
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class BenchmarkCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:rule-dev:benchmark')
            ->addArgument('count', InputArgument::OPTIONAL, 'Number of rules to benchmark', 100)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $input->getArgument('count');

        $generatorCommand = $this->getApplication()->find('pim:rule-dev:generate-fake');
        $arguments = array(
            'command' => 'pim:rule-dev:generate-fake',
            'count'   => $count
        );

        $input = new ArrayInput($arguments);
        $generatorCommand->run($input, $output);

        $runnerCommand = $this->getApplication()->find('pim:rule:run');
        $cpt = 0;

        $progress = $this->getHelper('progress');
        $progress->start($output, $count);
        while ($cpt < $count) {
            $arguments = array(
                'command' => 'pim:rule:run',
                'code'    => 'rule_' . $cpt
            );
            $input = new ArrayInput($arguments);
            $runnerCommand->run($input, $output);

            $progress->advance();
            $cpt++;
        }

        $progress->finish();

        $stopWatch = $this->getContainer()->get('debug.stopwatch');

        $events = $stopWatch->getSectionEvents('__root__');


        $stats = [];

        foreach ($events as $name => $event) {
            if (!isset($stats[$event->getCategory()])) {
                $stats[$event->getCategory()] = [
                    'duration' => 0
                ];
            }

            $stats[$event->getCategory()]['duration'] += $event->getDuration();

            echo sprintf("%s : \t%sms, %smb\n", $name, $event->getDuration(), $event->getMemory() / 1000000);
        }

        echo "\n";

        foreach ($stats as $category => $stat) {
            echo sprintf("Category %s : \t%sms\n", $category, $stat['duration']);
        }
    }
}
