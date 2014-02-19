<?php

namespace spec\Pim\Bundle\ImportExportBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Symfony\Component\Translation\TranslatorInterface;

class JobExecutionNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer, TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);

        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_job_execution(JobExecution $jobExecution)
    {
        $this->supportsNormalization($jobExecution)->shouldBe(true);
    }

    function it_normalizes_a_job_execution_instance(
        JobExecution $jobExecution,
        StepExecution $exportExecution,
        StepExecution $cleanExecution,
        $serializer,
        $translator
    ) {
        $jobExecution->getFailureExceptions()->willReturn(
            [
                ['message' => 'error', 'messageParameters' => ['foo' => 'bar']]
            ]
        );
        $translator->trans('error', ['foo' => 'bar'], 'messages', 'en_US')->willReturn('Such error');

        $jobExecution->getLabel()->willReturn('Wow job');

        $jobExecution->getStepExecutions()->willReturn([$exportExecution, $cleanExecution]);
        $serializer->normalize($exportExecution, 'any', ['translationDomain' => 'messages', 'translationLocale' => 'en_US'])->willReturn('**exportExecution**');
        $serializer->normalize($cleanExecution, 'any', ['translationDomain' => 'messages', 'translationLocale' => 'en_US'])->willReturn('**cleanExecution**');

        $this->normalize($jobExecution, 'any')->shouldReturn([
            'label'          => 'Wow job',
            'failures'       => ['Such error'],
            'stepExecutions' => ['**exportExecution**', '**cleanExecution**']
        ]);
    }

    function it_normalizes_a_job_execution_instance_using_context_parameters_to_translate_failure_exceptions(
        JobExecution $jobExecution,
        $translator
    ) {
        $jobExecution->getFailureExceptions()->willReturn(
            [
                ['message' => 'error', 'messageParameters' => ['foo' => 'bar']]
            ]
        );
        $translator->trans('error', ['foo' => 'bar'], 'job', 'ce_ZN')->willReturn('Such error');

        $jobExecution->getLabel()->willReturn('Wow job');

        $jobExecution->getStepExecutions()->willReturn([]);

        $this->normalize($jobExecution, 'any', ['translationDomain' => 'job', 'translationLocale' => 'ce_ZN'])->shouldReturn([
            'label'          => 'Wow job',
            'failures'       => ['Such error'],
            'stepExecutions' => []
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

        $exception = new \RuntimeException('Cannot normalize job execution of "My Job" because injected serializer is not a normalizer');
        $this->shouldThrow($exception)->duringNormalize($jobExecution, 'any');
    }
}
