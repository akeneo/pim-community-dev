<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\StepExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PhpSpec\ObjectBehavior;

class JobExecutionTrackingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $firstStepExecutionTracking = new StepExecutionTracking(
            1,
            10,
            0,
            false,
            0,
            0,
            false,
            Status::fromLabel('COMPLETED'),
        );

        $secondStepExecutionTracking = new StepExecutionTracking(
            2,
            10,
            2,
            true,
            100,
            100,
            true,
            Status::fromLabel('COMPLETED'),
        );

        $this->beConstructedWith(2, 3, [$firstStepExecutionTracking, $secondStepExecutionTracking]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(JobExecutionTracking::class);
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'current_step' => 2,
            'total_step' => 3,
            'steps' => [
                [
                    'id' => 1,
                    'duration' => 10,
                    'warning_count' => 0,
                    'has_error' => false,
                    'total_items' => 0,
                    'processed_items' => 0,
                    'is_trackable' => false,
                    'status' => 'COMPLETED',
                ],
                [
                    'id' => 2,
                    'duration' => 10,
                    'warning_count' => 2,
                    'has_error' => true,
                    'total_items' => 100,
                    'processed_items' => 100,
                    'is_trackable' => true,
                    'status' => 'COMPLETED',
                ]
            ],
        ]);
    }

    public function it_returns_has_error()
    {
        $this->hasError()->shouldReturn(true);
    }

    public function it_returns_warning_count()
    {
        $this->getWarningCount()->shouldReturn(2);
    }

    public function it_can_be_constructed_only_with_a_list_of_step_execution_tracking()
    {
        $this->beConstructedWith(1, 3, [1, 2, 3]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
