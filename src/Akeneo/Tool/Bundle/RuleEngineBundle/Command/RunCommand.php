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

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Command;

use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\BulkDryRunnerInterface;
use Symfony\Component\Console\Command\Command;
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
class RunCommand extends Command
{
    protected static $defaultName = 'akeneo:rule:run';

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var BulkDryRunnerInterface */
    private $strictbBulkDryRunner;

    /** @var BulkDryRunnerInterface */
    private $bulkDryRunner;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        BulkDryRunnerInterface $strictbBulkDryRunner,
        BulkDryRunnerInterface $bulkDryRunner
    ) {
        parent::__construct();

        $this->eventDispatcher = $eventDispatcher;
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->strictbBulkDryRunner = $strictbBulkDryRunner;
        $this->bulkDryRunner = $bulkDryRunner;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
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

        $this->eventDispatcher->addListener(
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
        if (null !== $ruleCode) {
            $rules = $this->ruleDefinitionRepository->findBy(
                ['code' => explode(',', $ruleCode)],
                ['priority' => 'DESC']
            );

            if (empty($rules)) {
                throw new \InvalidArgumentException(sprintf('The rule(s) %s does not exists', $ruleCode));
            }
        } else {
            $rules = $this->ruleDefinitionRepository->findAllOrderedByPriority();
        }

        return $rules;
    }

    /**
     * @param bool $stopOnError
     *
     * @return BulkDryRunnerInterface
     */
    protected function getRuleRunner($stopOnError): BulkDryRunnerInterface
    {
        if ($stopOnError) {
            return $this->strictbBulkDryRunner;
        }

        return $this->bulkDryRunner;
    }
}
