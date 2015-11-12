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
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
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
            ->setName('akeneo:rule:run')
            ->addArgument('code', InputArgument::OPTIONAL, 'Code of the rule to run')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop rules execution on error')
            ->setDescription('Runs all the rules or only one if a code is provided.')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->hasArgument('code') ? $input->getArgument('code') : null;
        $rules = $this->getRulesToRun($code);
        $runnerRegistry = $this->getRuleRunner();

        $stopOnError = $input->getOption('stop-on-error') ?: false;
        $dryRun = $input->getOption('dry-run') ?: false;

        $message = $dryRun ? 'Dry running rule <info>%s</info>...' : 'Running rule <info>%s</info>...';

        foreach ($rules as $rule) {
            $output->writeln(sprintf($message, $rule->getCode()));
            $this->runRule(
                $runnerRegistry,
                $output,
                $rule,
                $dryRun,
                $stopOnError
            );
        }

        $output->writeln('<info>Done !</info>');
    }

    /**
     * Run a single rule
     *
     * @param DryRunnerInterface      $runnerRegistry
     * @param OutputInterface         $output
     * @param RuleDefinitionInterface $rule
     * @param bool                    $dryRun
     * @param bool                    $stopOnError
     *
     * @throws \Exception
     */
    protected function runRule(
        DryRunnerInterface $runnerRegistry,
        OutputInterface $output,
        RuleDefinitionInterface $rule,
        $dryRun,
        $stopOnError
    ) {
        try {
            if ($dryRun) {
                $subjectSet = $runnerRegistry->dryRun($rule);
                $message = '<info>%d</info> subjects impacted by the rule <info>%s</info>.';
                $output->writeln(sprintf($message, count($subjectSet->getSubjectsCursor()), $rule->getCode()));
                $output->writeln('');
            } else {
                $runnerRegistry->run($rule);
            }
        } catch (\Exception $e) {
            if ($stopOnError) {
                throw $e;
            } else {
                $message = '<error>Error</error> during the execution of the rule <info>%s</info>: <error>%s</error>.';
                $output->writeln(sprintf($message, $rule->getCode(), $e->getMessage()));
                $output->writeln('');
            }
        }
    }

    /**
     * @param $ruleCode
     *
     * @return RuleDefinitionInterface[]
     */
    protected function getRulesToRun($ruleCode)
    {
        $repository = $this->getRuleDefinitionRepository();

        if (null !== $ruleCode) {
            $rule = $repository->findOneBy(['code' => $ruleCode]);

            if (null === $rule) {
                throw new \InvalidArgumentException(sprintf('The rule %s does not exists', $ruleCode));
            }

            $rules = [$rule];
        } else {
            $rules = $repository->findAllOrderedByPriority();
        }

        return $rules;
    }

    /**
     * @return RuleDefinitionRepositoryInterface
     */
    protected function getRuleDefinitionRepository()
    {
        return $this->getContainer()->get('akeneo_rule_engine.repository.rule_definition');
    }

    /**
     * @return DryRunnerInterface
     */
    protected function getRuleRunner()
    {
        return $this->getContainer()->get('akeneo_rule_engine.runner.chained');
    }
}
