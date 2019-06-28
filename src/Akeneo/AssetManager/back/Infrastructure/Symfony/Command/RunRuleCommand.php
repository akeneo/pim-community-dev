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

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command;

use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\BulkDryRunnerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;

/**
 * Command to run a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RunRuleCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:asset-manager:run-rules')
            ->addArgument('rules', InputArgument::REQUIRED, 'JSON representing the rules to execute')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop rules execution on error')
            ->setDescription('Runs the rules provided as JSON.')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rules = $input->getArgument('rules');
        $stopOnError = $input->getOption('stop-on-error') ?: false;
        $dryRun = $input->getOption('dry-run') ?: false;

        $message = $dryRun ? 'Dry running rules...' : 'Running rules...';
        $output->writeln($message);

        try {
            echo $rules;
            $normalizedRules = json_decode($rules, true);
        } catch (\Exception $exception) {
            $output->writeln('Error during parsing provided JSON');
            return;
        }

        $rules = $this->getRulesToRun($normalizedRules);

        $progressBar = new ProgressBar($output, count($rules));

        $this->getContainer()
            ->get('event_dispatcher')
            ->addListener(
                RuleEvents::POST_EXECUTE,
                function () use ($progressBar) {
                    $progressBar->advance();
                }
            );

        $this->runRules($rules, $dryRun, $stopOnError);

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
    protected function getRulesToRun(array $normalizedRules): array
    {
        return array_map(function (array $normalizedRule) {
            $rule = new RuleDefinition();
            $rule->setCode($normalizedRule['code']);
            $rule->setType($normalizedRule['type']);
            $rule->setPriority($normalizedRule['priority']);
            $rule->setContent($normalizedRule['content']);

            return $rule;
        }, $normalizedRules);
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
