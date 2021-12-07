<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRowTracking;
use Akeneo\Platform\Job\Application\SearchJobExecution\StepExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PhpSpec\ObjectBehavior;

class JobExecutionRowTrackingSpec extends ObjectBehavior
{
    public function let(): void  {
        $firstStepExecutionTracking = new StepExecutionTracking(
            1,
            10,
            0,
            0,
            0,
            0,
            false,
            Status::fromLabel('COMPLETED'),
        );

        $secondStepExecutionTracking = new StepExecutionTracking(
            2,
            10,
            2,
            3,
            100,
            100,
            true,
            Status::fromLabel('COMPLETED'),
        );

        $this->beConstructedWith(2, 3, [$firstStepExecutionTracking, $secondStepExecutionTracking]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(JobExecutionRowTracking::class);
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
                    'error_count' => 0,
                    'total_items' => 0,
                    'processed_items' => 0,
                    'is_trackable' => false,
                    'status' => 'COMPLETED',
                ],
                [
                    'id' => 2,
                    'duration' => 10,
                    'warning_count' => 2,
                    'error_count' => 3,
                    'total_items' => 100,
                    'processed_items' => 100,
                    'is_trackable' => true,
                    'status' => 'COMPLETED',
                ]
            ],
        ]);
    }

    public function it_returns_error_count() {
        $this->getErrorCount()->shouldReturn(3);
    }

    public function it_can_be_constructed_only_with_a_list_of_step_execution_tracking()
    {
        $this->beConstructedWith(1, 3, [1, 2, 3]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
