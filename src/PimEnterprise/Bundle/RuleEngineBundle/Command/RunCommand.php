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

class RunCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:rule:run')
            ->addArgument('code', InputArgument::REQUIRED, 'Rule code')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get rule instance
        $code = $input->getArgument('code');
        $repo = $this->getContainer()->get('pimee_rule_engine.repository.rule');
        $rule = $repo->findOneByCode($code);

        // load/prepare the rule
        $loaderRegistry = $this->getContainer()->get('pimee_rule_engine.loader.chained');
        $businessRule = $loaderRegistry->load($rule);

        // run the rule
        $runnerRegistry = $this->getContainer()->get('pimee_rule_engine.runner.chained');
        $runnerRegistry->run($businessRule);
    }
}
