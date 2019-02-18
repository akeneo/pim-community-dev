<?php

namespace spec\Akeneo\Tool\Bundle\BatchQueueBundle\Manager;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use DateInterval;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JobExecutionManagerSpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, Connection $connection)
    {
        $entityManager->getConnection()->willReturn($connection);
        $this->beConstructedWith($entityManager);
    }

    function it_does_not_modify_status_when_a_job_execution_has_not_been_launched(
        JobExecution $jobExecution,
        BatchStatus $status,
        ExitStatus $exitStatus
    ) {
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $status->getValue()->willReturn(BatchStatus::STARTING);
        $exitStatus->isRunning()->willReturn(false);

        $jobExecution->setStatus(Argument::any())->shouldNotBeCalled();
        $jobExecution->setExitStatus(Argument::any())->shouldNotBeCalled();

        $this->resolveJobExecutionStatus($jobExecution);
    }

    function it_does_not_modify_status_when_a_job_execution_is_completed(
        JobExecution $jobExecution,
        BatchStatus $status,
        ExitStatus $exitStatus
    ) {
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $status->getValue()->willReturn(BatchStatus::COMPLETED);
        $exitStatus->isRunning()->willReturn(false);

        $jobExecution->setStatus(Argument::any())->shouldNotBeCalled();
        $jobExecution->setExitStatus(Argument::any())->shouldNotBeCalled();

        $this->resolveJobExecutionStatus($jobExecution);
    }

    function it_resolves_job_execution_status_when_job_execution_failed_but_has_still_a_running_status(
        JobExecution $jobExecution,
        BatchStatus $status,
        ExitStatus $exitStatus
    ) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $healthCheck = new \DateTime('now', new \DateTimeZone('UTC'));
        $healthCheck->add(DateInterval::createFromDateString('-100 seconds'));

        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $jobExecution->getHealthCheckTime()->willReturn($healthCheck);

        $status->getValue()->willReturn(BatchStatus::STARTED);
        $exitStatus->isRunning()->willReturn(true);

        $jobExecution->setStatus(new BatchStatus(BatchStatus::FAILED))->shouldBeCalled();
        $jobExecution->setExitStatus(new ExitStatus(ExitStatus::FAILED))->shouldBeCalled();

        $this->resolveJobExecutionStatus($jobExecution);
    }

    function it_resolves_job_execution_status_when_job_execution_failed_with_null_health_check(
        JobExecution $jobExecution,
        BatchStatus $status,
        ExitStatus $exitStatus
    ) {
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $jobExecution->getHealthCheckTime()->willReturn(null);

        $status->getValue()->willReturn(BatchStatus::STARTED);
        $exitStatus->isRunning()->willReturn(true);

        $this->resolveJobExecutionStatus($jobExecution);
    }

    function it_gets_exit_status(
        $connection,
        JobExecutionMessage $jobExecutionMessage,
        Statement $stmt
    ) {
        $connection
            ->prepare(Argument::type('string'))
            ->willReturn($stmt);

        $jobExecutionMessage->getJobExecutionId()->willReturn(1);
        $stmt->bindValue('id', 1)->shouldBeCalled();
        $stmt->execute()->shouldBeCalled();
        $stmt->fetch()->willReturn(['exit_code' => 'COMPLETED']);

        $this->getExitStatus($jobExecutionMessage)->shouldBeLike(new ExitStatus('COMPLETED'));
    }

    function it_marks_as_failed(
        $connection,
        JobExecutionMessage $jobExecutionMessage,
        Statement $stmt
    ) {
        $connection
            ->prepare(Argument::type('string'))
            ->willReturn($stmt);

        $jobExecutionMessage->getJobExecutionId()->willReturn(1);

        $stmt->bindValue('id', 1)->shouldBeCalled();
        $stmt->bindValue('status', BatchStatus::FAILED)->shouldBeCalled();
        $stmt->bindValue('exit_code', ExitStatus::FAILED)->shouldBeCalled();
        $stmt->bindValue('updated_time', Argument::type(\DateTime::class), Type::DATETIME)->shouldBeCalled();
        $stmt->execute()->shouldBeCalled();

        $this->markAsFailed($jobExecutionMessage);
    }

    function it_updates_healthcheck(
        $connection,
        JobExecutionMessage $jobExecutionMessage,
        Statement $stmt
    ) {
        $connection
            ->prepare(Argument::type('string'))
            ->willReturn($stmt);

        $jobExecutionMessage->getJobExecutionId()->willReturn(1);

        $stmt->bindValue('id', 1)->shouldBeCalled();
        $stmt->bindValue('health_check_time', Argument::type(\DateTime::class), Type::DATETIME)->shouldBeCalled();
        $stmt->bindValue('updated_time', Argument::type(\DateTime::class), Type::DATETIME)->shouldBeCalled();
        $stmt->execute()->shouldBeCalled();

        $this->updateHealthCheck($jobExecutionMessage);
    }
}
