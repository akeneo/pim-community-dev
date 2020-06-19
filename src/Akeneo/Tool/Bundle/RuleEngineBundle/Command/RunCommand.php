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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
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

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('code', InputArgument::OPTIONAL, 'Code(s) of the rule(s) to run')
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
        $ruleCodes = null === $code ? [] : explode(',', $code);
        $username = $input->getOption('username') ?: null;
        $stopOnError = $input->getOption('stop-on-error') ?: false;
        $dryRun = $input->getOption('dry-run') ?: false;

        $config = [
            'rule_codes' => $ruleCodes,
            'user_to_notify' => $username,
            'stop_on_error' => $stopOnError,
            'dry_run' => $dryRun,
        ];

        $params = [
            'command' => 'akeneo:batch:job',
            'code' => 'rule_engine_execute_rules',
            '--no-debug' => true,
            '--no-log' => true,
            '--config' => json_encode($config),
        ];
        if (null !== $username) {
            $params['--username'] = $username;
        }

        $message = $dryRun ? 'Dry running rules...' : 'Running rules...';
        $output->writeln($message);

        $progressBar = new ProgressBar($output, count($ruleCodes));

        $this->eventDispatcher->addListener(
                RuleEvents::POST_EXECUTE,
                function () use ($progressBar) {
                    $progressBar->advance();
                }
            );

        $this->getApplication()->setAutoExit(false);
        $result = $this->getApplication()->run(new ArrayInput($params), new NullOutput());

        $progressBar->finish();
        $output->writeln('');

        return $result;
    }
}
