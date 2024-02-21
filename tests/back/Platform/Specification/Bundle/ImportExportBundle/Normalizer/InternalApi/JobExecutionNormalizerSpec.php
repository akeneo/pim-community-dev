<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Normalizer\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Model\JobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Normalizer\InternalApi\JobExecutionNormalizer;
use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetJobExecutionTracking;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class JobExecutionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $jobExecutionStandardNormalizer,
        UserContext $userContext,
        GetJobExecutionTracking $getJobExecutionTracking,
        NormalizerInterface $jobExecutionTrackingNormalizer
    ) {
        $this->beConstructedWith(
            $jobExecutionStandardNormalizer,
            $userContext,
            $getJobExecutionTracking,
            $jobExecutionTrackingNormalizer
        );
    }

    function it_is_a_job_execution_normalizer()
    {
        $this->shouldBeAnInstanceOf(JobExecutionNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_a_internal_api_normalizer()
    {
        $jobExecution = new JobExecution();
        $this->supportsNormalization($jobExecution, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($jobExecution, 'standard')->shouldReturn(false);

        $object = new \stdClass();
        $this->supportsNormalization($object, 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($object, 'standard')->shouldReturn(false);
    }

    function it_normalizes_a_job_execution(
        NormalizerInterface $jobExecutionStandardNormalizer,
        UserContext $userContext,
        GetJobExecutionTracking $getJobExecutionTracking,
        NormalizerInterface $jobExecutionTrackingNormalizer,
        JobExecution $jobExecution,
        JobExecutionTracking $jobExecutionTracking
    ) {
        $jobExecution->getId()->willReturn(1);

        $userContext->getUserTimezone()->willReturn('Europe/Paris');
        $userContext->getUiLocaleCode()->willReturn('en_US');

        $jobExecutionStandardNormalizer
            ->normalize($jobExecution, 'standard', ['locale' => 'en_US', 'timezone' => 'Europe/Paris'])
            ->willReturn([
                'failures'       => ['Such error'],
                'stepExecutions' => ['**exportExecution**', '**cleanExecution**'],
                'isRunning'      => true,
                'status'         => 'COMPLETED',
                'jobInstance'    => ['Normalized job instance with datetime in user timezone']
            ]);

        $getJobExecutionTracking
            ->execute(1)
            ->willReturn($jobExecutionTracking);

        $jobExecutionTrackingNormalizer
            ->normalize($jobExecutionTracking)
            ->willReturn([
                'status' => 'IN_PROGRESS',
                'currentStep' => 1,
                'totalSteps' => 2,
                'steps' => [
                    ['name' => 'foo'],
                    ['name' => 'bar'],
                ]
            ]);

        $this->normalize($jobExecution, 'internal_api')->shouldReturn([
            'failures'       => ['Such error'],
            'stepExecutions' => ['**exportExecution**', '**cleanExecution**'],
            'isRunning'      => true,
            'status'         => 'COMPLETED',
            'jobInstance'    => ['Normalized job instance with datetime in user timezone'],
            'tracking'       => [
                'status' => 'IN_PROGRESS',
                'currentStep' => 1,
                'totalSteps' => 2,
                'steps' => [
                    ['name' => 'foo'],
                    ['name' => 'bar'],
                ]
            ],
        ]);
    }

    function it_normalizes_a_job_execution_without_user_in_the_user_context(
        NormalizerInterface $jobExecutionStandardNormalizer,
        UserContext $userContext,
        GetJobExecutionTracking $getJobExecutionTracking,
        NormalizerInterface $jobExecutionTrackingNormalizer,
        JobExecution $jobExecution,
        JobExecutionTracking $jobExecutionTracking
    ) {
        $jobExecution->getId()->willReturn(1);

        $userContext->getUserTimezone()->willThrow(\RuntimeException::class);

        $getJobExecutionTracking
            ->execute(1)
            ->willReturn($jobExecutionTracking);

        $jobExecutionTrackingNormalizer
            ->normalize($jobExecutionTracking)
            ->willReturn('NORMALIZED_JOB_EXECUTION_TRACKING_PLACEHOLDER');

        $jobExecutionStandardNormalizer
            ->normalize($jobExecution, 'standard', [])
            ->willReturn([
                'failures'       => ['Such error'],
                'stepExecutions' => ['**exportExecution**', '**cleanExecution**'],
                'isRunning'      => true,
                'status'         => 'COMPLETED',
                'jobInstance'    => ['Normalized job instance with datetime in server timezone']
            ]);

        $this->normalize($jobExecution, 'internal_api')->shouldReturn([
            'failures'       => ['Such error'],
            'stepExecutions' => ['**exportExecution**', '**cleanExecution**'],
            'isRunning'      => true,
            'status'         => 'COMPLETED',
            'jobInstance'    => ['Normalized job instance with datetime in server timezone'],
            'tracking'       => 'NORMALIZED_JOB_EXECUTION_TRACKING_PLACEHOLDER',
        ]);
    }
}
