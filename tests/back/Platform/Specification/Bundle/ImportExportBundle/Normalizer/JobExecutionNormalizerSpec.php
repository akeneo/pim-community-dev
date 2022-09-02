<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Normalizer;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class JobExecutionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        TranslatorInterface $translator,
        NormalizerInterface $jobInstanceNormalizer,
        JobRegistry $jobRegistry
    ) {
        $this->beConstructedWith($translator, $jobInstanceNormalizer, $jobRegistry);

        $normalizer->implement(NormalizerInterface::class);
        $this->setNormalizer($normalizer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerAwareInterface::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_normalization_of_job_execution(JobExecution $jobExecution)
    {
        $this->supportsNormalization($jobExecution)->shouldBe(true);
    }

    function it_normalizes_a_job_execution_instance(
        NormalizerInterface $normalizer,
        TranslatorInterface $translator,
        NormalizerInterface $jobInstanceNormalizer,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        StepExecution $exportExecution,
        StepExecution $cleanExecution,
        BatchStatus $status
    ) {
        $jobExecution->getFailureExceptions()->willReturn(
            [
                ['message' => 'error', 'messageParameters' => ['foo' => 'bar']]
            ]
        );
        $jobInstance->getJobName()->willReturn('wow_job');
        $translator->trans('error', ['foo' => 'bar'])->willReturn('Such error');

        $jobExecution->isRunning()->willReturn(true);
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $status->getValue()->willReturn(1);
        $translator->trans('pim_import_export.batch_status.1')->willReturn('COMPLETED');

        $jobExecution->getStepExecutions()->willReturn([$exportExecution, $cleanExecution]);
        $normalizer->normalize($exportExecution, 'any', [])->willReturn('**exportExecution**');
        $normalizer->normalize($cleanExecution, 'any', [])->willReturn('**cleanExecution**');
        $jobInstanceNormalizer->normalize($jobInstance, 'standard', Argument::cetera())->willReturn(['Normalized job instance']);

        $this->normalize($jobExecution, 'any')->shouldReturn([
            'failures'       => ['Such error'],
            'stepExecutions' => ['**exportExecution**', '**cleanExecution**'],
            'isRunning'      => true,
            'isStoppable'    => false,
            'status'         => 'COMPLETED',
            'jobInstance'    => ['Normalized job instance']
        ]);
    }

    function it_normalizes_a_stoppable_job_execution_instance(
        NormalizerInterface $normalizer,
        TranslatorInterface $translator,
        NormalizerInterface $jobInstanceNormalizer,
        JobRegistry $jobRegistry,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        StepExecution $exportExecution,
        StepExecution $cleanExecution,
        BatchStatus $status,
        Job $job
    ) {
        $jobExecution->getFailureExceptions()->willReturn(
            [
                ['message' => 'error', 'messageParameters' => ['foo' => 'bar']]
            ]
        );
        $jobInstance->getJobName()->willReturn('wow_job');
        $translator->trans('error', ['foo' => 'bar'])->willReturn('Such error');

        $jobRegistry->has('wow_job')->willReturn(true);
        $jobRegistry->get('wow_job')->willReturn($job);
        $job->isStoppable()->willReturn(true);
        $jobExecution->isRunning()->willReturn(true);
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $status->getValue()->willReturn(1);
        $translator->trans('pim_import_export.batch_status.1')->willReturn('COMPLETED');

        $jobExecution->getStepExecutions()->willReturn([$exportExecution, $cleanExecution]);
        $normalizer->normalize($exportExecution, 'any', [])->willReturn('**exportExecution**');
        $normalizer->normalize($cleanExecution, 'any', [])->willReturn('**cleanExecution**');
        $jobInstanceNormalizer->normalize($jobInstance, 'standard', Argument::cetera())->willReturn(['Normalized job instance']);

        $this->normalize($jobExecution, 'any')->shouldReturn([
            'failures'       => ['Such error'],
            'stepExecutions' => ['**exportExecution**', '**cleanExecution**'],
            'isRunning'      => true,
            'isStoppable'    => true,
            'status'         => 'COMPLETED',
            'jobInstance'    => ['Normalized job instance']
        ]);
    }
}
