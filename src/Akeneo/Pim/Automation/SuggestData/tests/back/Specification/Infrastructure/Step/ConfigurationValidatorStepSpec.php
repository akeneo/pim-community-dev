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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Step;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\GetSuggestDataConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Doctrine\IdentifiersMappingRepository;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Step\ConfigurationValidatorStep;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ConfigurationValidatorStepSpec extends ObjectBehavior
{
    public function let(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        GetSuggestDataConnectionStatus $connectionStatus,
        IdentifiersMappingRepository $identifiersMappingRepo
    ): void {
        $this->beConstructedWith(
            'validate_configuration',
            $eventDispatcher,
            $jobRepository,
            $connectionStatus,
            $identifiersMappingRepo
        );
    }

    public function it_is_a_step(): void
    {
        $this->shouldImplement(StepInterface::class);
        $this->shouldHaveType(AbstractStep::class);
    }

    public function it_is_a_token_validator_step(): void
    {
        $this->shouldHaveType(ConfigurationValidatorStep::class);
    }

    public function it_stops_execution_with_an_invalid_token(
        $eventDispatcher,
        $jobRepository,
        $connectionStatus,
        StepExecution $execution,
        BatchStatus $status
    ): void {
        $connectionStatus->isActive()->willReturn(false);
        $this->failure($eventDispatcher, $jobRepository, $execution, $status);

        $this->execute($execution);
    }

    public function it_stops_execution_with_empty_identifiers_mapping(
        $eventDispatcher,
        $jobRepository,
        $connectionStatus,
        $identifiersMappingRepo,
        StepExecution $execution,
        BatchStatus $status
    ): void {
        $connectionStatus->isActive()->willReturn(true);
        $identifiersMappingRepo->find()->willReturn(new IdentifiersMapping([]));

        $this->failure($eventDispatcher, $jobRepository, $execution, $status);

        $this->execute($execution);
    }

    public function it_stops_execution_with_incomplete_identifiers_mapping(
        $eventDispatcher,
        $jobRepository,
        $connectionStatus,
        $identifiersMappingRepo,
        StepExecution $execution,
        BatchStatus $status,
        AttributeInterface $brand
    ): void {
        $connectionStatus->isActive()->willReturn(true);
        $identifiersMappingRepo->find()->willReturn(new IdentifiersMapping(['brand' => $brand]));

        $this->failure($eventDispatcher, $jobRepository, $execution, $status);

        $this->execute($execution);
    }

    public function it_executes_with_success(
        $eventDispatcher,
        $jobRepository,
        $connectionStatus,
        $identifiersMappingRepo,
        StepExecution $execution,
        BatchStatus $status,
        ExitStatus $exitStatus,
        AttributeInterface $asin
    ): void {
        $connectionStatus->isActive()->willReturn(true);
        $identifiersMappingRepo->find()->willReturn(new IdentifiersMapping(['asin' => $asin]));
        $execution->addSummaryInfo('configuration_validation', 'OK')->shouldBeCalled();

        $this->success($eventDispatcher, $jobRepository, $execution, $status, $exitStatus);

        $this->execute($execution);
    }

    /**
     * Common assertions for a successful execution
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface $jobRepository
     * @param StepExecution $execution
     * @param BatchStatus $status
     * @param ExitStatus $exitStatus
     */
    private function success(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        StepExecution $execution,
        BatchStatus $status,
        ExitStatus $exitStatus
    ): void {
        $execution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::STARTING);

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
    }

    /**
     * Common assertions for a failed validation
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface $jobRepository
     * @param StepExecution $execution
     * @param BatchStatus $status
     */
    private function failure(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        StepExecution $execution,
        BatchStatus $status
    ): void {
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
    }
}
