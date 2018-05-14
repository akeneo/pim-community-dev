<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use PhpSpec\ObjectBehavior;

class BatchStatusSpec extends ObjectBehavior
{
    function it_is_displayable()
    {
        $this->beConstructedWith(BatchStatus::ABANDONED);
        $this->__toString()->shouldReturn('ABANDONED');
    }

    function it_is_updatable()
    {
        $this->beConstructedWith(BatchStatus::UNKNOWN);
        $this->__toString()->shouldReturn('UNKNOWN');
        $this->setValue(BatchStatus::ABANDONED);
        $this->__toString()->shouldReturn('ABANDONED');
    }

    function it_returns_largest_of_two_values()
    {
        $this::max(BatchStatus::FAILED, BatchStatus::COMPLETED)->shouldReturn(BatchStatus::FAILED);
        $this::max(BatchStatus::COMPLETED, BatchStatus::FAILED)->shouldReturn(BatchStatus::FAILED);
        $this::max(BatchStatus::FAILED, BatchStatus::FAILED)->shouldReturn(BatchStatus::FAILED);
        $this::max(BatchStatus::STARTED, BatchStatus::STARTING)->shouldReturn(BatchStatus::STARTED);
        $this::max(BatchStatus::COMPLETED, BatchStatus::STARTED)->shouldReturn(BatchStatus::STARTED);
    }

    function it_upgrades_finished_value_when_already_failed()
    {
        $this->beConstructedWith(BatchStatus::FAILED);
        $this->upgradeTo(BatchStatus::COMPLETED);
        $this->__toString()->shouldReturn('FAILED');
    }

    function it_upgrades_finished_value_when_already_completed()
    {
        $this->beConstructedWith(BatchStatus::COMPLETED);
        $this->upgradeTo(BatchStatus::FAILED);
        $this->__toString()->shouldReturn('FAILED');
    }

    function it_upgrades_unfinished_value_when_starting()
    {
        $this->beConstructedWith(BatchStatus::STARTING);
        $this->upgradeTo(BatchStatus::COMPLETED);
        $this->__toString()->shouldReturn('COMPLETED');
    }

    function it_upgrades_unfinished_value_when_completed()
    {
        $this->beConstructedWith(BatchStatus::COMPLETED);
        $this->upgradeTo(BatchStatus::STARTING);
        $this->__toString()->shouldReturn('COMPLETED');
    }

    function it_is_not_running_when_failed()
    {
        $this->beConstructedWith(BatchStatus::FAILED);
        $this->isRunning()->shouldReturn(false);
    }

    function it_is_not_running_when_completed()
    {
        $this->beConstructedWith(BatchStatus::COMPLETED);
        $this->isRunning()->shouldReturn(false);
    }

    function it_is_running_when_started()
    {
        $this->beConstructedWith(BatchStatus::STARTED);
        $this->isRunning()->shouldReturn(true);
    }

    function it_is_running_when_starting()
    {
        $this->beConstructedWith(BatchStatus::STARTING);
        $this->isRunning()->shouldReturn(true);
    }

    function it_is_unsuccessful_when_failed()
    {
        $this->beConstructedWith(BatchStatus::FAILED);
        $this->isUnsuccessful()->shouldReturn(true);
    }

    function it_is_successful_when_completed()
    {
        $this->beConstructedWith(BatchStatus::COMPLETED);
        $this->isUnsuccessful()->shouldReturn(false);
    }

    function it_is_successful_when_started()
    {
        $this->beConstructedWith(BatchStatus::STARTED);
        $this->isUnsuccessful()->shouldReturn(false);
    }

    function it_is_successful_when_starting()
    {
        $this->beConstructedWith(BatchStatus::STARTING);
        $this->isUnsuccessful()->shouldReturn(false);
    }

    function it_provides_status_labels()
    {
        $this::getAllLabels()->shouldReturn(
            [
                'COMPLETED' => BatchStatus::COMPLETED,
                'STARTING' => BatchStatus::STARTING,
                'STARTED' => BatchStatus::STARTED,
                'STOPPING' => BatchStatus::STOPPING,
                'STOPPED' => BatchStatus::STOPPED,
                'FAILED' => BatchStatus::FAILED,
                'ABANDONED' => BatchStatus::ABANDONED,
                'UNKNOWN' => BatchStatus::UNKNOWN
            ]
        );
    }
}
