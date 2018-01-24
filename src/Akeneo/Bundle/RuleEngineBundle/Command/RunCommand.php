<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Command;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\BulkDryRunnerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Username of the user to notify once rule(s) executed.'
            )
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
        $username = $input->getOption('username') ?: null;
        $stopOnError = $input->getOption('stop-on-error') ?: false;
        $dryRun = $input->getOption('dry-run') ?: false;

        $message = $dryRun ? 'Dry running rules...' : 'Running rules...';
        $output->writeln($message);

        $rules = $this->getRulesToRun($code);

        $progressBar = new ProgressBar($output, count($rules));

        $this->getContainer()
            ->get('event_dispatcher')
            ->addListener(
                RuleEvents::POST_EXECUTE,
                function () use ($progressBar) {
                    $progressBar->advance();
                }
            );

        $this->runRules($rules, $dryRun, $stopOnError, $username);

        $progressBar->finish();
    }

    /**
     * @param RuleDefinitionInterface[] $rules
     * @param bool                      $dryRun
     * @param bool                      $stopOnError
     * @param string|null               $username
     *
     * @throws \Exception
     */
    protected function runRules(array $rules, $dryRun, $stopOnError, $username = null)
    {
        $chainedRunner = $this->getRuleRunner($stopOnError);

        $options = [];
        if (null !== $username) {
            $options['username'] = $username;
        }

        $dryRun ? $chainedRunner->dryRunAll($rules) : $chainedRunner->runAll($rules, $options);
    }

    /**
     * @param string $ruleCode
     *
     * @return RuleDefinitionInterface[]
     */
    protected function getRulesToRun($ruleCode): array
    {
        $repository = $this->getRuleDefinitionRepository();

        if (null !== $ruleCode) {
            $rules = $repository->findBy(
                ['code' => explode(',', $ruleCode)],
                ['priority' => 'DESC']
            );

            if (empty($rules)) {
                throw new \InvalidArgumentException(sprintf('The rule(s) %s does not exists', $ruleCode));
            }
        } else {
            $rules = $repository->findAllOrderedByPriority();
        }

        return $rules;
    }

    /**
     * @return RuleDefinitionRepositoryInterface
     */
    protected function getRuleDefinitionRepository(): RuleDefinitionRepositoryInterface
    {
        return $this->getContainer()->get('akeneo_rule_engine.repository.rule_definition');
    }

    /**
     * @param bool $stopOnError
     *
     * @return BulkDryRunnerInterface
     */
    protected function getRuleRunner($stopOnError): BulkDryRunnerInterface
    {
        if ($stopOnError) {
            return $this->getContainer()->get('akeneo_rule_engine.runner.strict_chained');
        }

        return $this->getContainer()->get('akeneo_rule_engine.runner.chained');
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->getContainer()->get('event_dispatcher');
    }
}
