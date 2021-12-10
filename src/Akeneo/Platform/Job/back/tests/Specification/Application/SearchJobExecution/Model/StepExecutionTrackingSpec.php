<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\StepExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PhpSpec\ObjectBehavior;

class StepExecutionTrackingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            1,
            10,
            0,
            0,
            200,
            100,
            true,
            Status::fromLabel('IN_PROGRESS'),
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(StepExecutionTracking::class);
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'id' => 1,
            'duration' => 10,
            'warning_count' => 0,
            'error_count' => 0,
            'total_items' => 200,
            'processed_items' => 100,
            'is_trackable' => true,
            'status' => 'IN_PROGRESS',
        ]);
    }

    public function it_returns_id()
    {
        $this->getId()->shouldReturn(1);
    }

    public function it_returns_error_count()
    {
        $this->getErrorCount()->shouldReturn(0);
    }

    public function it_returns_warning_count()
    {
        $this->getWarningCount()->shouldReturn(0);
    }
}
