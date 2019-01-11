<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Step;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Step\IdentifiersMappingValidationStep;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingValidationStepSpec extends ObjectBehavior
{
    public function let(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        GetConnectionStatusHandler $connectionStatusHandler
    ): void {
        $this->beConstructedWith(
            'validate_identifiers_mapping',
            $eventDispatcher,
            $jobRepository,
            $connectionStatusHandler
        );
    }

    public function it_is_a_step(): void
    {
        $this->shouldImplement(StepInterface::class);
        $this->shouldHaveType(AbstractStep::class);
    }

    public function it_is_a_token_validator_step(): void
    {
        $this->shouldHaveType(IdentifiersMappingValidationStep::class);
    }

    public function it_stops_execution_if_identifiers_mapping_is_not_valid(
        $eventDispatcher,
        $jobRepository,
        $connectionStatusHandler,
        StepExecution $execution,
        BatchStatus $status
    ): void {
        $connectionStatusHandler
            ->handle(new GetConnectionStatusQuery(false))
            ->willReturn(new ConnectionStatus(true, false, false, 0));

        $execution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::STARTING);

        $eventDispatcher->dispatch(EventInterface::BEFORE_STEP_EXECUTION, Argument::any())->shouldBeCalled();
        $execution->setStartTime(Argument::type(\DateTime::class))->shouldBeCalled();
        $execution->setStatus(Argument::type(BatchStatus::class))->shouldBeCalled();
        $jobRepository->updateStepExecution($execution)->shouldBeCalled();

        $execution->upgradeStatus(BatchStatus::FAILED)->shouldBeCalled();
        $execution->addFailureException(Argument::type(\Exception::class))->shouldBeCalled();
        $jobRepository->updateStepExecution($execution)->shouldBeCalled();

        $eventDispatcher->dispatch(EventInterface::STEP_EXECUTION_ERRORED, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(EventInterface::STEP_EXECUTION_COMPLETED, Argument::any())->shouldBeCalled();

        $execution->setEndTime(Argument::type(\DateTime::class))->shouldBeCalled();
        $execution->setExitStatus(Argument::type(ExitStatus::class))->shouldBeCalled();

        $this->execute($execution);
    }

    public function it_executes_with_success(
        $eventDispatcher,
        $jobRepository,
        $connectionStatusHandler,
        StepExecution $execution,
        BatchStatus $status,
        ExitStatus $exitStatus
    ): void {
        $execution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::STARTING);
        $connectionStatusHandler
            ->handle(new GetConnectionStatusQuery(false))
            ->willReturn(new ConnectionStatus(true, false, true, 0));

        $execution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);

        $eventDispatcher->dispatch(EventInterface::BEFORE_STEP_EXECUTION, Argument::any())->shouldBeCalled();
        $execution->setStartTime(Argument::type(\DateTime::class))->shouldBeCalled();
        $execution->setStatus(Argument::type(BatchStatus::class))->shouldBeCalled();
        $jobRepository->updateStepExecution($execution)->shouldBeCalled();

        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(EventInterface::STEP_EXECUTION_SUCCEEDED, Argument::any())->shouldBeCalled();

        $eventDispatcher->dispatch(EventInterface::STEP_EXECUTION_COMPLETED, Argument::any())->shouldBeCalled();

        $execution->setEndTime(Argument::type(\DateTime::class))->shouldBeCalled();
        $execution->setExitStatus(Argument::type(ExitStatus::class))->shouldBeCalled();

        $execution->addSummaryInfo('identifiers_mapping_validation', 'OK')->shouldBeCalled();

        $this->execute($execution);
    }
}
