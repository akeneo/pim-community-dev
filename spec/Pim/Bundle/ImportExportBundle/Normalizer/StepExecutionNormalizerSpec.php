<?php

namespace spec\Pim\Bundle\ImportExportBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

class StepExecutionNormalizerSpec extends ObjectBehavior
{
    public function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_step_execution(StepExecution $stepExecution)
    {
        $this->supportsNormalization($stepExecution)->shouldBe(true);
    }

    function it_normalizes_a_step_execution(
        StepExecution $stepExecution,
        BatchStatus $status,
        \DateTime $startTime,
        $translator
    ) {
        $stepExecution->getStepName()->willReturn('export');
        $translator->trans('export', [], 'messages', 'en_US')->willReturn('Export step');

        $stepExecution->getSummary()->willReturn(['read' => 12, 'write' => 50]);
        $translator->trans('read', [], 'messages', 'en_US')->willReturn('Read');
        $translator->trans('write', [], 'messages', 'en_US')->willReturn('Write');

        $stepExecution->getStatus()->willReturn($status);
        $status->__toString()->willReturn('PENDING');

        $stepExecution->getStartTime()->willReturn($startTime);
        $stepExecution->getEndTime()->willReturn(null);
        $startTime->format('Y-m-d H:i:s')->willReturn('yesterday');

        $stepExecution->getWarnings()->willReturn(
            [
                [
                    'name'             => 'a_warning',
                    'reason'           => 'warning_reason',
                    'reasonParameters' => ['foo' => 'bar'],
                    'item'             => ['a' => 'A', 'b' => 'B', 'c' => 'C'],
                ]
            ]
        );
        $translator->trans('a_warning', [], 'messages', 'en_US')->willReturn('Reader');
        $translator->trans('warning_reason', ['foo' => 'bar'], 'messages', 'en_US')->willReturn('WARNING!');

        $stepExecution->getFailureExceptions()->willReturn(
            [
                [
                    'message'           => 'a_failure',
                    'messageParameters' => ['foo' => 'bar'],
                ]
            ]
        );
        $translator->trans('a_failure', ['foo' => 'bar'], 'messages', 'en_US')->willReturn('FAIL!');

        $this->normalize($stepExecution, 'any')->shouldReturn(
            [
               'label'     => 'Export step',
               'status'    => 'PENDING',
               'summary'   => ['Read' => 12, 'Write' => 50],
               'startedAt' => 'yesterday',
               'endedAt'   => null,
               'warnings'  => [
                   [
                       'label'  => 'Reader',
                       'reason' => 'WARNING!',
                       'item'   => ['a' => 'A', 'b' => 'B', 'c' => 'C'],
                   ]
               ],
               'failures'  => ['FAIL!'],
            ]
        );
    }

    function it_normalizes_a_step_execution_instance_using_context_parameters_to_translate(
        StepExecution $stepExecution,
        BatchStatus $status,
        $translator
    ) {
        $stepExecution->getStepName()->willReturn('export');
        $translator->trans('export', [], 'step', 'fr_FR')->willReturn('Export step');

        $stepExecution->getSummary()->willReturn([]);

        $stepExecution->getStatus()->willReturn($status);
        $status->__toString()->willReturn('PENDING');

        $stepExecution->getStartTime()->willReturn(null);
        $stepExecution->getEndTime()->willReturn(null);

        $stepExecution->getWarnings()->willReturn(
            [
                [
                    'name'             => 'a_warning',
                    'reason'           => 'warning_reason',
                    'reasonParameters' => ['foo' => 'bar'],
                    'item'             => ['a' => 'A', 'b' => 'B', 'c' => 'C'],
                ]
            ]
        );
        $translator->trans('a_warning', [], 'step', 'fr_FR')->willReturn('Reader');
        $translator->trans('warning_reason', ['foo' => 'bar'], 'step', 'fr_FR')->willReturn('WARNING!');

        $stepExecution->getFailureExceptions()->willReturn(
            [
                [
                    'message'           => 'a_failure',
                    'messageParameters' => ['foo' => 'bar'],
                ]
            ]
        );
        $translator->trans('a_failure', ['foo' => 'bar'], 'step', 'fr_FR')->willReturn('FAIL!');

        $this->normalize($stepExecution, 'any', ['translationDomain' => 'step', 'translationLocale' => 'fr_FR'])->shouldReturn(
            [
               'label'     => 'Export step',
               'status'    => 'PENDING',
               'summary'   => [],
               'startedAt' => null,
               'endedAt'   => null,
               'warnings'  => [
                   [
                       'label'  => 'Reader',
                       'reason' => 'WARNING!',
                       'item'   => ['a' => 'A', 'b' => 'B', 'c' => 'C'],
                   ]
               ],
               'failures'  => ['FAIL!'],
            ]
        );
    }
}
