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

use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SchedulePushStructureAndProductsToFranklinInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters\PushStructureAndProductsToFranklinParameters;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\User\UserInterface;

final class SchedulePushStructureAndProductsToFranklinSpec extends ObjectBehavior
{
    public function let(
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->beConstructedWith($queueJobLauncher, $jobInstanceRepository, $userRepository);
    }

    public function it_is_the_job_implementation_to_schedule_push_structure_and_products_to_franklin()
    {
        $this->shouldImplement(SchedulePushStructureAndProductsToFranklinInterface::class);
    }

    public function it_schedules_push_structure_and_products_to_franklin(
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserRepositoryInterface $userRepository,
        UserInterface $user
    ) {
        $userRepository->findOneByIdentifier(ProposalAuthor::USERNAME)->willReturn($user);

        $jobInstance = new JobInstance('Franklin Insights Connector', 'franklin_insights', JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS);
        $jobInstanceRepository->findOneByIdentifier(JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS)->willReturn($jobInstance);

        $jobParameters = [
            PushStructureAndProductsToFranklinParameters::ATTRIBUTES_BATCH_SIZE => 25,
            PushStructureAndProductsToFranklinParameters::FAMILIES_BATCH_SIZE => 10,
            PushStructureAndProductsToFranklinParameters::PRODUCTS_BATCH_SIZE => 100,
        ];

        $queueJobLauncher->launch($jobInstance, $user, $jobParameters)->shouldBeCalled();

        $this->schedule(new BatchSize(25), new BatchSize(10), new BatchSize(100));
    }

    public function it_throws_an_exception_if_the_job_instance_has_not_been_found(JobInstanceRepository $jobInstanceRepository)
    {
        $jobInstanceRepository->findOneByIdentifier(JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS)->willReturn(null);

        $this->shouldThrow(\RuntimeException::class)->during('schedule', [new BatchSize(25), new BatchSize(10), new BatchSize(100)]);
    }

    public function it_throws_an_exception_if_the_franklin_user_has_not_been_found(
        JobInstanceRepository $jobInstanceRepository,
        UserRepositoryInterface $userRepository
    ) {
        $jobInstance = new JobInstance('Franklin Insights Connector', 'franklin_insights', JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS);
        $jobInstanceRepository->findOneByIdentifier(JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS)->willReturn($jobInstance);

        $userRepository->findOneByIdentifier(ProposalAuthor::USERNAME)->willReturn(null);

        $this->shouldThrow(\RuntimeException::class)->during('schedule', [new BatchSize(25), new BatchSize(10), new BatchSize(100)]);
    }
}
