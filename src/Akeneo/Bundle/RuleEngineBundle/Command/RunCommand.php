<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Command;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RunCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:rule:run')
            ->addArgument('code', InputArgument::OPTIONAL, 'Rule code')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop rules execution on error')
            ->setDescription('Run all rules or only one if a code is provided.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->getContainer()->get('akeneo_rule_engine.repository.rule_definition');

        // get rule instances
        if ($code = $input->getArgument('code')) {
            $rule = $repo->findOneByCode($code);

            if (null === $rule) {
                throw new \InvalidArgumentException(sprintf('The rule %s does not exists', $code));
            }

            $rules = [$rule];
        } else {
            $rules = $repo->findAll();
        }

        // run the rules
        $runnerRegistry = $this->getContainer()->get('akeneo_rule_engine.runner.chained');

        foreach ($rules as $rule) {
            $this->runRule(
                $runnerRegistry,
                $output,
                $rule,
                $input->getOption('dry-run'),
                $input->getOption('stop-on-error')
            );
        }
    }

    /**
     * Run a single rule
     *
     * @param DryRunnerInterface $runnerRegistry
     * @param OutputInterface    $output
     * @param RuleDefinition     $rule
     * @param bool               $dryRun
     * @param bool               $stopOnError
     *
     * @throws \Exception
     */
    protected function runRule(
        DryRunnerInterface $runnerRegistry,
        OutputInterface $output,
        RuleDefinition $rule,
        $dryRun,
        $stopOnError
    ) {
        try {
            if ($dryRun) {
                $runnerRegistry->dryRun($rule);
            } else {
                $runnerRegistry->run($rule);
            }
        } catch (\Exception $e) {
            if ($stopOnError) {
                throw $e;
            } else {
                $output->writeln(
                    sprintf(
                        "Error during execution of rule %s : %s\n",
                        $rule->getCode(),
                        $e->getMessage()
                    )
                );
            }
        }
    }
}
