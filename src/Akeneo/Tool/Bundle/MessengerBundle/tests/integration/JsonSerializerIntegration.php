<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\integration;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Akeneo\Tool\Bundle\MessengerBundle\Serialization\JsonSerializer;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ExportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ImportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Envelope;
use Webmozart\Assert\Assert;

/**
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
}
