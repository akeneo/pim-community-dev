<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class JobContext implements Context
{
    private const JOB_IDENTIFIER = 'rule_engine_execute_rules';

    /** @var JobLauncher */
    protected $jobLauncherTest;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var UserProviderInterface */
    private $userProvider;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    public function __construct(
        JobLauncher $jobLauncherTest,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserProviderInterface $userProvider,
        EntityManagerClearerInterface $entityManagerClearer
    ) {
        $this->jobLauncherTest = $jobLauncherTest;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->userProvider = $userProvider;
        $this->entityManagerClearer = $entityManagerClearer;
    }

    /**
     * @When I launch the rule execution job
     */
    public function iLaunchTheRuleExecutionJob(): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(static::JOB_IDENTIFIER);
        if (null === $jobInstance) {
            throw new \RuntimeException(sprintf('The "%s" job instance is not found.', static::JOB_IDENTIFIER));
        }

        $user = $this->userProvider->loadUserByUsername('admin');
        $jobExecution = $this->jobLauncher->launch($jobInstance, $user, []);
        $this->jobLauncherTest->launchConsumerOnce();
        $this->jobLauncherTest->waitCompleteJobExecution($jobExecution);
        $this->entityManagerClearer->clear();
    }
}
