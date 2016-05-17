<?php

namespace spec\Pim\Bundle\ImportExportBundle\Normalizer;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Job\BatchStatus;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Provider\JobLabelProvider;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class JobExecutionNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer, TranslatorInterface $translator, JobLabelProvider $labelProvider)
    {
        $this->beConstructedWith($translator, $labelProvider);

        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer');
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_job_execution(JobExecution $jobExecution)
    {
        $this->supportsNormalization($jobExecution)->shouldBe(true);
    }

    function it_normalizes_a_job_execution_instance(
        $serializer,
        $translator,
        $labelProvider,
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
        $jobInstance->getAlias()->willReturn('wow_job');
        $translator->trans('error', ['foo' => 'bar'])->willReturn('Such error');
        $labelProvider->getJobLabel('wow_job')->willReturn('Wow job');

        $jobExecution->isRunning()->willReturn(true);
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $status->getValue()->willReturn(1);
        $translator->trans('pim_import_export.batch_status.1')->willReturn('COMPLETED');

        $jobExecution->getStepExecutions()->willReturn([$exportExecution, $cleanExecution]);
        $serializer->normalize($exportExecution, 'any', [])->willReturn('**exportExecution**');
        $serializer->normalize($cleanExecution, 'any', [])->willReturn('**cleanExecution**');

        $this->normalize($jobExecution, 'any')->shouldReturn([
            'label'          => 'Wow job',
            'failures'       => ['Such error'],
            'stepExecutions' => ['**exportExecution**', '**cleanExecution**'],
            'isRunning'      => true,
            'status'         => 'COMPLETED',
        ]);
    }

    function it_throws_exception_when_serializer_is_not_a_normalizer(
        JobExecution $jobExecution,
        SerializerInterface $nonNormalizeSerializer,
        $exportExecution,
        $cleanExecution
    ) {
        $this->setSerializer($nonNormalizeSerializer);
        $jobExecution->getStepExecutions()->willReturn([$exportExecution, $cleanExecution]);
        $jobExecution->getFailureExceptions()->willReturn([]);
        $jobExecution->getLabel()->willReturn('My Job');

        $this
            ->shouldThrow(
                new \RuntimeException(
                    'Cannot normalize job execution of "My Job" because injected serializer is not a normalizer'
                )
            )
            ->duringNormalize($jobExecution, 'any');
    }
}
