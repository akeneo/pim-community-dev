<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionHealthCheck;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PhpSpec\ObjectBehavior;

class JobExecutionHealthCheckSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::COMPLETED),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->shouldBeAnInstanceOf(JobExecutionHealthCheck::class);
    }

    public function it_resolves_the_current_status_if_job_is_completed()
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::COMPLETED),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->resolveStatus()->shouldBeLike(Status::fromStatus(Status::COMPLETED));
    }

    public function it_resolves_the_current_status_if_job_is_failed()
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::FAILED),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->resolveStatus()->shouldBeLike(Status::fromStatus(Status::FAILED));
    }

    public function it_resolves_the_current_status_if_job_is_stopped()
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::STOPPED),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->resolveStatus()->shouldBeLike(Status::fromStatus(Status::STOPPED));
    }

    public function it_resolves_the_current_status_if_job_is_abandoned()
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::ABANDONED),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->resolveStatus()->shouldBeLike(Status::fromStatus(Status::ABANDONED));
    }

    public function it_resolves_the_current_status_if_job_is_unknown()
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::UNKNOWN),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->resolveStatus()->shouldBeLike(Status::fromStatus(Status::UNKNOWN));
    }


    public function it_resolves_the_failed_status_if_job_is_starting_and_health_check_is_more_than_10_seconds_ago()
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::STARTING),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->resolveStatus()->shouldBeLike(Status::fromStatus(Status::FAILED));
    }

    public function it_resolves_the_failed_status_if_job_is_in_progress_and_health_check_is_more_than_10_seconds_ago()
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::IN_PROGRESS),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->resolveStatus()->shouldBeLike(Status::fromStatus(Status::FAILED));
    }

    public function it_resolves_the_failed_status_if_job_is_stopping_and_health_check_is_more_than_10_seconds_ago()
    {
        $this->beConstructedWith(
            Status::fromStatus(Status::STOPPING),
            new \DateTimeImmutable('2021-11-02T12:00:05+02:00'),
            new \DateTimeImmutable('2021-11-02T12:00:20+02:00')
        );

        $this->resolveStatus()->shouldBeLike(Status::fromStatus(Status::FAILED));
    }
}
