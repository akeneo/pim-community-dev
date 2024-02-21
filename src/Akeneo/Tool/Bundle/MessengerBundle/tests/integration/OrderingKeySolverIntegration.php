<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\integration;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ExportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ImportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Envelope;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class OrderingKeySolverIntegration extends KernelTestCase
{
    private OrderingKeySolver $orderingKeySolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->orderingKeySolver = self::getContainer()->get(OrderingKeySolver::class);
    }

    public function test_it_returns_null_for_an_unknown_message(): void
    {
        self::assertNull($this->orderingKeySolver->solve(new Envelope(new \stdClass)));
    }

    /**
     * @dataProvider jobMessagesProvider
     */
    public function test_it_returns_an_ordering_key_for_job_messages(
        JobExecutionMessageInterface $jobExecutionMessage
    ): void {
        self::assertSame('job_key', $this->orderingKeySolver->solve(new Envelope($jobExecutionMessage)));
    }

    public function jobMessagesProvider(): array
    {
        return [
            [UiJobExecutionMessage::createJobExecutionMessage(1, [])],
            [ImportJobExecutionMessage::createJobExecutionMessage(1, [])],
            [ExportJobExecutionMessage::createJobExecutionMessage(1, [])],
            [DataMaintenanceJobExecutionMessage::createJobExecutionMessage(1, [])],
        ];
    }
}
