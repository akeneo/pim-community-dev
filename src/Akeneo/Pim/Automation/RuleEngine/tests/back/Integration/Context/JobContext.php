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

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Webmozart\Assert\Assert;

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

    private JobExecution $jobExecution;

    public function __construct(
        JobLauncher $jobLauncherTest,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserProviderInterface $userProvider,
        EntityManagerClearerInterface $entityManagerClearer,
        private JobExecutionRepository $executionRepository
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

        $user = $this->userProvider->loadUserByIdentifier('admin');
        $this->jobExecution = $this->jobLauncher->launch($jobInstance, $user, []);
        $this->jobLauncherTest->launchConsumerOnce();
        $this->jobLauncherTest->waitCompleteJobExecution($this->jobExecution);
        $this->entityManagerClearer->clear();
    }

    /**
     * @Then /^([0-9]+) products? should have been skipped with no update$/
     */
    public function productsShouldHaveBeenWithNoUpdate(int $count):void
    {
        $jobExecution = $this->executionRepository->findOneBy(['id' => $this->jobExecution->getId()]);
        $stepExecutions = $jobExecution->getStepExecutions();
        foreach ($stepExecutions as $stepExecution) {
            $skippedNoDiff = $stepExecution->getSummary()['skipped_no_diff'] ?? null;
            if (null !== $skippedNoDiff) {
                Assert::same($skippedNoDiff, $count);
                return;
            }
        }
        throw new \Exception('Cannot find skipped product info');
    }
}
