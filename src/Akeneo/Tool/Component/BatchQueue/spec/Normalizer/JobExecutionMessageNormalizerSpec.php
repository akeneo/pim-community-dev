<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\BatchQueue\Normalizer;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JobExecutionMessageNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
    }

    function it_supports_job_messenger_normalization_only()
    {
        $jobMessenger = JobExecutionMessage::createJobExecutionMessage(1, []);

        $this->supportsNormalization($jobMessenger, '')->shouldBe(true);
        $this->supportsNormalization(new \StdClass(), '')->shouldBe(false);
    }

    function it_normalizes_a_simple_job_messenger()
    {
        $createdAt = new \DateTime('2020-01-01');
        $jobMessenger = JobExecutionMessage::createJobExecutionMessage(
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
        $jobMessenger = JobExecutionMessage::createJobExecutionMessageFromDatabase(
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
        $this->supportsDenormalization([], JobExecutionMessage::class)->shouldBe(true);
        $this->supportsDenormalization([], 'Unknown')->shouldBe(false);
    }

    function it_denormalizes_a_simple_job_messenger()
    {
        $normalized = [
            'id' => null,
            'job_execution_id' => 1,
            'consumer' => null,
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => null,
            'options' => ['option1' => 'value1'],
        ];

        $jobMessenger = $this->denormalize($normalized, JobExecutionMessage::class);
        $jobMessenger->shouldBeAnInstanceOf(JobExecutionMessage::class);
        $jobMessenger->getId()->shouldBeNull();
        $jobMessenger->getJobExecutionId()->shouldBe(1);
        $jobMessenger->getConsumer()->shouldBeNull();
        $jobMessenger->getCreateTime()->shouldBeLike(new \DateTime('2021-03-08T15:37:23+01:00'));
        $jobMessenger->getUpdatedTime()->shouldBeNull();
        $jobMessenger->getOptions()->shouldBe(['option1' => 'value1']);
    }

    function it_denormalizes_a_full_job_messenger()
    {
        $normalized = [
            'id' => 1,
            'job_execution_id' => 2,
            'consumer' => 'consumer_name',
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => '2021-03-08T15:58:12+01:00',
            'options' => ['option1' => 'value1'],
        ];

        $jobMessenger = $this->denormalize($normalized, JobExecutionMessage::class);
        $jobMessenger->shouldBeAnInstanceOf(JobExecutionMessage::class);
        $jobMessenger->getId()->shouldBe(1);
        $jobMessenger->getJobExecutionId()->shouldBe(2);
        $jobMessenger->getConsumer()->shouldBe('consumer_name');
        $jobMessenger->getCreateTime()->shouldBeLike(new \DateTime('2021-03-08T15:37:23+01:00'));
        $jobMessenger->getUpdatedTime()->shouldBeLike(new \DateTime('2021-03-08T15:58:12+01:00'));
        $jobMessenger->getOptions()->shouldBe(['option1' => 'value1']);
    }
}
