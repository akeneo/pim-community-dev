<?php

namespace spec\Pim\Bundle\ImportExportBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

class StepExecutionNormalizerSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
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
        $translator->trans('export')->willReturn('Export step');

        $stepExecution->getSummary()->willReturn(['read' => 12, 'write' => 50]);
        $translator->trans('job_execution.summary.read')->willReturn('Read');
        $translator->trans('job_execution.summary.write')->willReturn('Write');

        $stepExecution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(9);
        $translator->trans('pim_import_export.batch_status.9')->willReturn('PENDING');

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
        $translator->trans('a_warning')->willReturn('Reader');
        $translator->trans('warning_reason', ['foo' => 'bar'])->willReturn('WARNING!');

        $stepExecution->getFailureExceptions()->willReturn(
            [
                [
                    'message'           => 'a_failure',
                    'messageParameters' => ['foo' => 'bar'],
                ]
            ]
        );
        $translator->trans('a_failure', ['foo' => 'bar'])->willReturn('FAIL!');

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
}
