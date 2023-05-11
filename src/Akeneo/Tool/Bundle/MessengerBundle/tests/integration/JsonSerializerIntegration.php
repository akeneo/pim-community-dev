<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\integration;

use Akeneo\Tool\Bundle\MessengerBundle\Serialization\JsonSerializer;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JsonSerializerIntegration extends KernelTestCase
{
    private JsonSerializer $jsonSerializer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->jsonSerializer = self::getContainer()->get('akeneo_batch_queue.messenger.serializer');
    }

    public function test_it_decodes_a_job_execution_message(): void
    {
        $decoded = $this->jsonSerializer->decode([
            'body' => \json_encode([
                'id' => Uuid::fromString('76bdf33d-1b32-4781-9645-5b903e013f5f'),
                'job_execution_id' => 666,
                'updated_time' => null,
            ]),
            'headers' => [
                'tenant_id' => 'srnt-foo',
                'class' => DataMaintenanceJobExecutionMessage::class,
            ],
        ]);

        Assert::isInstanceOf($decoded, Envelope::class);

        /** @var JobExecutionMessageInterface $message */
        $message = $decoded->getMessage();
        Assert::isInstanceOf($message, JobExecutionMessageInterface::class);
        Assert::eq($message->getId(), Uuid::fromString('76bdf33d-1b32-4781-9645-5b903e013f5f'));
        Assert::same($message->getJobExecutionId(), 666);
        Assert::null($message->getTenantId());
    }

    public function test_it_encodes_a_job_execution_message(): void
    {
        $jobExecutionMessage = DataMaintenanceJobExecutionMessage::createJobExecutionMessage(666, ['opt1']);
        $envelope = new Envelope(
            $jobExecutionMessage,
            [new TenantIdStamp('srnt-foo')]
        );

        $decoded = $this->jsonSerializer->encode($envelope);

        Assert::isArray($decoded);

        Assert::string($decoded['body']);
        $body = \json_decode($decoded['body'], true);
        Assert::keyExists($body, 'id');
        Assert::keyExists($body, 'job_execution_id');
        Assert::keyExists($body, 'created_time');
        Assert::keyExists($body, 'updated_time');
        Assert::keyExists($body, 'options');
        Assert::same($body['job_execution_id'], 666);
        Assert::null($body['updated_time']);
        Assert::same($body['options'], ['opt1']);

        Assert::same($decoded['headers']['class'], DataMaintenanceJobExecutionMessage::class);
        Assert::same($decoded['headers']['tenant_id'], 'srnt-foo');
    }

    public function test_it_decodes_a_scheduled_job_message(): void
    {
        $decoded = $this->jsonSerializer->decode([
            'body' => \json_encode([
                'job_code' => 'foo_job_code',
                'options' => ['--option1', '--option2'],
            ]),
            'headers' => [
                'tenant_id' => 'srnt-foo',
                'class' => ScheduledJobMessage::class,
            ],
        ]);

        Assert::isInstanceOf($decoded, Envelope::class);

        /** @var ScheduledJobMessage $message */
        $message = $decoded->getMessage();
        Assert::isInstanceOf($message, ScheduledJobMessage::class);
        Assert::same($message->getJobCode(), 'foo_job_code');
        Assert::same($message->getOptions(), ['--option1', '--option2']);
        Assert::null($message->getTenantId());
    }

    public function test_it_encodes_a_scheduled_job_message(): void
    {
        $scheduledJobMessage = ScheduledJobMessage::createScheduledJobMessage('foo_job_code', ['opt1']);
        $envelope = new Envelope(
            $scheduledJobMessage,
            [new TenantIdStamp('srnt-foo')]
        );

        $decoded = $this->jsonSerializer->encode($envelope);

        Assert::isArray($decoded);

        Assert::string($decoded['body']);
        $body = \json_decode($decoded['body'], true);
        Assert::keyExists($body, 'job_code');
        Assert::keyExists($body, 'options');
        Assert::same($body['job_code'], 'foo_job_code');
        Assert::same($body['options'], ['opt1']);

        Assert::same($decoded['headers']['class'], ScheduledJobMessage::class);
        Assert::same($decoded['headers']['tenant_id'], 'srnt-foo');
    }
}
