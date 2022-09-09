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

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RunCommand extends Command
{
    protected static $defaultName = 'akeneo:rule:run';

    public function __construct(
        private LoggerInterface $logger,
        private JobLauncherInterface $jobLauncher,
        private JobInstanceRepository $jobInstanceRepository,
        private UserRepositoryInterface $userRepository
    ) {
        parent::__construct();
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->hasArgument('code') ? $input->getArgument('code') : null;
        $ruleCodes = null === $code ? [] : explode(',', $code);
        $username = $input->getOption('username') ?: null;
        $stopOnError = $input->getOption('stop-on-error') ?: false;
        $dryRun = $input->getOption('dry-run') ?: false;

        $config = [
            'rule_codes' => $ruleCodes,
            'users_to_notify' => [$username],
            'stop_on_error' => $stopOnError,
            'dry_run' => $dryRun
        ];

        $user = null;
        if (null !== $username) {
            $user = $this->userRepository->findOneByIdentifier($username);
        }

        $this->logger->notice($dryRun ? 'Dry running rules...' : 'Running rules...');
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('rule_engine_execute_rules');
        $jobExecution = $this->jobLauncher->launch($jobInstance, $user, $config);

        $hasError = $jobExecution->getStatus()->isUnsuccessful();

        return $hasError ? self::FAILURE : self::SUCCESS;
    }
}
