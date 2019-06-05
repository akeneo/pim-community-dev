<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ScheduleFetchProductsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher\ScheduleFetchProductsJob;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class ScheduleFetchProductsJobSpec extends ObjectBehavior
{
    public function let(
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserRepositoryInterface $userRepository,
        JobExecutionMessageRepository $jobExecutionMessageRepository
    ): void {
        $this->beConstructedWith($queueJobLauncher, $jobInstanceRepository, $userRepository, $jobExecutionMessageRepository);
    }

    public function it_is_the_job_implementation_to_schedule_fetch_products(): void
    {
        $this->shouldImplement(ScheduleFetchProductsInterface::class);
        $this->shouldHaveType(ScheduleFetchProductsJob::class);
    }

    public function it_schedule_a_fetch_products_job(
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserRepositoryInterface $userRepository,
        JobExecutionMessageRepository $jobExecutionMessageRepository,
        UserInterface $user
    ): void {
        $jobExecutionMessageRepository
            ->getAvailableJobExecutionMessageFilteredByCodes([JobInstanceNames::FETCH_PRODUCTS])
            ->willReturn(null);

        $jobInstance = new JobInstance('Franklin Insights Connector', 'franklin_insights', 'franklin_insights_fetch_products');
        $jobInstanceRepository->findOneByIdentifier(JobInstanceNames::FETCH_PRODUCTS)->willReturn($jobInstance);
        $userRepository->findOneByIdentifier(ProposalAuthor::USERNAME)->willReturn($user);

        $queueJobLauncher->launch($jobInstance, $user)->shouldBeCalled();

        $this->schedule();
    }

    public function it_does_nothing_if_a_fetch_products_job_is_already_scheduled(
        JobLauncherInterface $queueJobLauncher,
        JobExecutionMessageRepository $jobExecutionMessageRepository,
        JobExecutionMessage $message
    ): void {
        $jobExecutionMessageRepository
            ->getAvailableJobExecutionMessageFilteredByCodes([JobInstanceNames::FETCH_PRODUCTS])
            ->willReturn($message);

        $queueJobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->schedule();
    }
}
