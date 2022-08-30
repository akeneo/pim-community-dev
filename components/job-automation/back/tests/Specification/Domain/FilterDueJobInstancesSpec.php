<?php

namespace Specification\Akeneo\Platform\JobAutomation\Domain;

use Akeneo\Platform\JobAutomation\Domain\CronExpressionFactory;
use Akeneo\Platform\JobAutomation\Domain\Model\CronExpression;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use PhpSpec\ObjectBehavior;

class FilterDueJobInstancesSpec extends ObjectBehavior
{
    function let(
        CronExpressionFactory $cronExpressionFactory
    ) {
        $this->beConstructedWith($cronExpressionFactory);
    }

    public function it_returns_only_due_scheduled_jobs_based_on_its_cron_expression(
        $cronExpressionFactory,
        CronExpression $cron1,
        CronExpression $cron2,
        CronExpression $cron3,
        CronExpression $cron4,
    ) {
        $cronExpressionFactory->fromExpression('0 0/4 * * *')->willReturn($cron1);
        $cronExpressionFactory->fromExpression('0 0/12 * * *')->willReturn($cron2);
        $cronExpressionFactory->fromExpression('0 0/8 * * *')->willReturn($cron3);
        $cronExpressionFactory->fromExpression('0 0/2 * * *')->willReturn($cron4);

        $scheduledJobInstances = [
            // No last execution date yet, so should be due
            new ScheduledJobInstance('job_1', 'a_job','import', [], true, '0 0/4 * * *', new \DateTimeImmutable('2022-10-30 00:00'), null, 'job_automated_job_1'),
            // Last execution date is in the past, so should be due
            new ScheduledJobInstance('job_2', 'a_job','import', [], true, '0 0/12 * * *', new \DateTimeImmutable('2022-10-30'), new \DateTimeImmutable('2022-10-30 00:00'), 'job_automated_job_2'),
            // Too early for the next execution, so should not be due (next will be at 16:00)
            new ScheduledJobInstance('job_3', 'a_job','import', [], true,'0 0/8 * * *', new \DateTimeImmutable('2022-10-30'), new \DateTimeImmutable('2022-10-30 08:00'), 'job_automated_job_3'),
            // Configured too late to be run, so should not be due (next will be at 14:00)
            new ScheduledJobInstance('job_4', 'a_job','import', [], true,'0 0/2 * * *', new \DateTimeImmutable('2022-10-30 12:34'), null, 'job_automated_job_4'),
        ];

        // For this test, let's says it's now 2020-10-30 13:00
        $cron1->isDue()->willReturn(true);
        $cron1->getPreviousRunDate()->willReturn(new \DateTime('2022-10-30 12:00'));
        $cron2->isDue()->willReturn(false);
        $cron2->getPreviousRunDate()->willReturn(new \DateTime('2022-10-30 12:00'));
        $cron3->isDue()->willReturn(false);
        $cron3->getPreviousRunDate()->willReturn(new \DateTime('2022-10-30 08:00'));
        $cron4->isDue()->willReturn(false);
        $cron4->getPreviousRunDate()->willReturn(new \DateTime('2022-10-30 12:00'));

        $this->fromScheduledJobInstances($scheduledJobInstances)->shouldBeLike([
            new ScheduledJobInstance('job_1', 'a_job','import', [], true, '0 0/4 * * *', new \DateTimeImmutable('2022-10-30 00:00'), null, 'job_automated_job_1'),
            new ScheduledJobInstance('job_2', 'a_job','import', [], true, '0 0/12 * * *', new \DateTimeImmutable('2022-10-30'), new \DateTimeImmutable('2022-10-30 00:00'), 'job_automated_job_2'),
        ]);
    }
}
