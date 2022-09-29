<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Domain;

use Akeneo\Platform\JobAutomation\Domain\CronExpressionFactory;
use Akeneo\Platform\JobAutomation\Domain\IsJobDue;
use Akeneo\Platform\JobAutomation\Domain\Model\CronExpression;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use PHPUnit\Framework\TestCase;

final class IsJobDueTest extends TestCase
{
    public function test_it_return_true_if_no_last_execution_date_yet(): void
    {
        $scheduledJobInstance = new ScheduledJobInstance('job_1', 'a_job', 'import', [], [], [], '0 */4 * * *', new \DateTimeImmutable('2022-10-30 00:01'), null, 'job_automated_job_1');
        $cronExpressionMock = $this->createMock(CronExpression::class);
        $cronExpressionMock->method('isDue')->willReturn(true);
        $cronExpressionMock->method('getPreviousRunDate')->willReturn(new \DateTime('2022-10-30 12:00'));
        $this->assertTrue(IsJobDue::fromScheduledJobInstance($scheduledJobInstance, $cronExpressionMock));
    }

    public function test_it_return_true_if_last_execution_date_is_in_the_past(): void
    {
        $scheduledJobInstance = new ScheduledJobInstance('job_2', 'a_job', 'import', [], [], [], '0 */12 * * *', new \DateTimeImmutable('2022-10-30'), new \DateTimeImmutable('2022-10-30 00:00'), 'job_automated_job_2');
        $cronExpressionMock = $this->createMock(CronExpression::class);
        $cronExpressionMock->method('isDue')->willReturn(false);
        $cronExpressionMock->method('getPreviousRunDate')->willReturn(new \DateTime('2022-10-30 12:00'));
        $this->assertTrue(IsJobDue::fromScheduledJobInstance($scheduledJobInstance, $cronExpressionMock));
    }

    public function test_it_return_false_if_too_early_for_the_next_execution(): void
    {
        $scheduledJobInstance = new ScheduledJobInstance('job_3', 'a_job', 'import', [], [], [], '0 */8 * * *', new \DateTimeImmutable('2022-10-30'), new \DateTimeImmutable('2022-10-30 08:00'), 'job_automated_job_3');
        $cronExpressionMock = $this->createMock(CronExpression::class);
        $cronExpressionMock->method('isDue')->willReturn(false);
        $cronExpressionMock->method('getPreviousRunDate')->willReturn(new \DateTime('2022-10-30 08:00'));
        $this->assertFalse(IsJobDue::fromScheduledJobInstance($scheduledJobInstance, $cronExpressionMock));
    }

    public function test_it_return_false_if_configured_too_late_to_be_run(): void
    {
        $scheduledJobInstance = new ScheduledJobInstance('job_4', 'a_job', 'import', [], [], [], '0 */2 * * *', new \DateTimeImmutable('2022-10-30 12:34'), null, 'job_automated_job_4');
        $cronExpressionMock = $this->createMock(CronExpression::class);
        $cronExpressionMock->method('isDue')->willReturn(false);
        $cronExpressionMock->method('getPreviousRunDate')->willReturn(new \DateTime('2022-10-30 12:00'));
        $this->assertFalse(IsJobDue::fromScheduledJobInstance($scheduledJobInstance, $cronExpressionMock));
    }
}
