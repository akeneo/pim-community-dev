<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;
use Symfony\Component\Console\Input\ArrayInput;

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

        while ($cpt < $count) {
            $arguments = array(
                'command' => 'pim:rule:run',
                'code'    => 'rule_' . $cpt
            );
            echo sprintf("### Running rule %s : \n", $cpt);
            $input = new ArrayInput($arguments);
            $runnerCommand->run($input, $output);

            echo "\n";
            $cpt++;
        }
    }
}
