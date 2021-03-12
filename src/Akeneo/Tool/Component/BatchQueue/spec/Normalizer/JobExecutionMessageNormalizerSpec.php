<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\BatchQueue\Normalizer;

use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\BackendJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ExportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ImportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JobExecutionMessageNormalizerSpec extends ObjectBehavior
{
    function let(JobExecutionMessageFactory $jobExecutionMessageFactory)
    {
        $this->beConstructedWith($jobExecutionMessageFactory);
    }

    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
    }

    function it_supports_job_messenger_normalization_only()
    {
        $jobMessenger = UiJobExecutionMessage::createJobExecutionMessage(1, []);
        $this->supportsNormalization($jobMessenger, '')->shouldBe(true);

        $jobMessenger = ImportJobExecutionMessage::createJobExecutionMessage(1, []);
        $this->supportsNormalization($jobMessenger, '')->shouldBe(true);

        $jobMessenger = ExportJobExecutionMessage::createJobExecutionMessage(1, []);
        $this->supportsNormalization($jobMessenger, '')->shouldBe(true);

        $jobMessenger = BackendJobExecutionMessage::createJobExecutionMessage(1, []);
        $this->supportsNormalization($jobMessenger, '')->shouldBe(true);

        $this->supportsNormalization(new \StdClass(), '')->shouldBe(false);
    }

    function it_normalizes_a_simple_job_messenger()
    {
        $jobMessenger = UiJobExecutionMessage::createJobExecutionMessage(
            1,
            ['option1' => 'value1']
        );

        $normalized = $this->normalize($jobMessenger);
        $normalized->shouldBeArray();
        $normalized['id']->shouldBeNull();
        $normalized['job_execution_id']->shouldBe(1);
        $normalized['consumer']->shouldBeNull();
        $normalized['created_time']->shouldNotBeNull();
        $normalized['updated_time']->shouldBeNull();
        $normalized['options']->shouldBe(['option1' => 'value1']);
    }

    function it_normalizes_a_full_job_messenger()
    {
        $createdAt = new \DateTime('2020-01-01');
        $updatedAt = new \DateTime('2020-02-01');
        $jobMessenger = ImportJobExecutionMessage::createJobExecutionMessageFromDatabase(
            1,
            2,
            'consumer',
            $createdAt,
            $updatedAt,
            ['option1' => 'value1']
        );

        $normalized = $this->normalize($jobMessenger);
        $normalized->shouldBeArray();
        $normalized['id']->shouldBe(1);
        $normalized['job_execution_id']->shouldBe(2);
        $normalized['consumer']->shouldBe('consumer');
        $normalized['created_time']->shouldBe($createdAt->format('c'));
        $normalized['updated_time']->shouldBe($updatedAt->format('c'));
        $normalized['options']->shouldBe(['option1' => 'value1']);
    }

    function it_supports_job_messenger_denormalization_only()
    {
        $this->supportsDenormalization([], UiJobExecutionMessage::class)->shouldBe(true);
        $this->supportsDenormalization([], ImportJobExecutionMessage::class)->shouldBe(true);
        $this->supportsDenormalization([], ExportJobExecutionMessage::class)->shouldBe(true);
        $this->supportsDenormalization([], BackendJobExecutionMessage::class)->shouldBe(true);
        $this->supportsDenormalization([], 'Unknown')->shouldBe(false);
    }

    function it_denormalizes_a_job_messenger(JobExecutionMessageFactory $jobExecutionMessageFactory)
    {
        $message = UiJobExecutionMessage::createJobExecutionMessage(1, []);
        $normalized = ['test'];
        $jobExecutionMessageFactory->buildFromNormalized($normalized)->willReturn($message);

        $this->denormalize($normalized, UiJobExecutionMessage::class)->shouldBe($message);
    }
}
