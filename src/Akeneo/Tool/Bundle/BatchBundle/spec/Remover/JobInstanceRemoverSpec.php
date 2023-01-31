<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Remover;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement\DeleteRunningUser;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\RemovableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobInstanceRemoverSpec extends ObjectBehavior
{
    function let(
        RemovableObjectRepositoryInterface $jobInstanceRepository,
        EventDispatcherInterface $eventDispatcher,
        DeleteRunningUser $deleteRunningUser,
        LoggerInterface $logger,
    ): void {
        $eventDispatcher->dispatch(Argument::any(), Argument::type('string'))->willReturn(Argument::type('object'));
        $this->beConstructedWith(
            $jobInstanceRepository,
            $eventDispatcher,
            $deleteRunningUser,
            $logger,
        );
    }

    function it_is_a_remover(): void
    {
        $this->shouldHaveType(RemoverInterface::class);
        $this->shouldHaveType(BulkRemoverInterface::class);
    }

    function it_removes_the_job_instance(RemovableObjectRepositoryInterface $jobInstanceRepository, JobInstance $jobInstance): void
    {
        $jobInstance->getId()->willReturn(1);
        $jobInstance->isScheduled()->willReturn(false);
        $jobInstanceCode = 'my_job';
        $jobInstance->getCode()->willReturn($jobInstanceCode);
        $jobInstanceRepository->remove($jobInstanceCode)->shouldBeCalled();

        $this->remove($jobInstance);
    }

    function it_removes_the_objects(RemovableObjectRepositoryInterface $jobInstanceRepository, JobInstance $jobInstance1, JobInstance $jobInstance2): void
    {
        $jobInstance1->getId()->willReturn(1);
        $jobInstanceCode1 = 'my_job1';
        $jobInstance1->getCode()->willReturn($jobInstanceCode1);

        $jobInstance2->getId()->willReturn(2);
        $jobInstanceCode2 = 'my_job2';
        $jobInstance2->getCode()->willReturn($jobInstanceCode2);

        $jobInstanceRepository->remove($jobInstanceCode1)->shouldBeCalled();
        $jobInstanceRepository->remove($jobInstanceCode2)->shouldBeCalled();

        $this->removeAll([$jobInstance1, $jobInstance2]);
    }

    function it_removes_the_running_user(
        JobInstance $jobInstance,
        DeleteRunningUser $deleteRunningUser,
    ): void
    {
        $jobInstance->getId()->willReturn(1);
        $jobInstance->isScheduled()->willReturn(true);
        $jobInstanceCode = 'my_job';
        $jobInstance->getCode()->willReturn($jobInstanceCode);
        $deleteRunningUser->execute($jobInstanceCode)->shouldBeCalled();

        $this->remove($jobInstance);
    }

    function it_throws_exception_when_remove_anything_else_than_a_job_instance(): void
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "%s", "%s" provided.',
                JobInstance::class,
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('remove', [$anythingElse]);
        $this->shouldThrow($exception)->during('removeAll', [[$anythingElse, $anythingElse]]);
    }
}
