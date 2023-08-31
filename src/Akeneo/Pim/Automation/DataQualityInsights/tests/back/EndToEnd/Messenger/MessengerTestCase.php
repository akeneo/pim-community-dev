<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

abstract class MessengerTestCase extends DataQualityInsightsTestCase
{
    private const MESSENGER_COMMAND_NAME = 'messenger:consume';

    /** @var PubSubQueueStatus */
    private array $pubSubQueueStatuses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pubSubQueueStatuses = [
            $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_launch_product_evaluations_consumer'),
            $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_attribute_group_activate_consumer'),
        ];

        $this->flushQueues();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->flushQueues();
    }

    private function flushQueues(): void
    {
        foreach ($this->pubSubQueueStatuses as $pubSubStatus) {
            $subscription = $pubSubStatus->getSubscription();
            try {
                $subscription->reload();
            } catch (\Exception) {
            }
            if (!$subscription->exists()) {
                continue;
            }

            do {
                $messages = $subscription->pull(['maxMessages' => 10, 'returnImmediately' => true]);
                $count = count($messages);
                if ($count > 0) {
                    $subscription->acknowledgeBatch($messages);
                }
            } while (0 < $count);
        }
    }

    protected function dispatchMessage(object $message): void
    {
        $this->get('messenger.bus.default')->dispatch($message);
    }

    protected function launchConsumer(string $consumerName, int $limit = 1): void
    {
        $command = [
            \sprintf('%s/bin/console', $this->getParameter('kernel.project_dir')),
            self::MESSENGER_COMMAND_NAME,
            \sprintf('--env=%s', $this->getParameter('kernel.environment')),
            \sprintf('--limit=%d', $limit),
            '-vvv',
            \sprintf('--time-limit=%d', 5),
            $consumerName,
            '--bus=pim_event.handle.bus'
        ];

        $process = new Process($command);
        $process->run();
        $process->wait();

        Assert::assertSame(0, $process->getExitCode(), 'An error occurred: ' . $process->getErrorOutput());
    }

    protected function assertProductScoreIsComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertTrue(
            $this->isProductScoreComputed($productUuid, $evaluatedAt),
            \sprintf('Product evaluation does not exist. Product uuid: %s', $productUuid->__toString())
        );
    }

    protected function assertProductScoreIsNotComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertFalse(
            $this->isProductScoreComputed($productUuid, $evaluatedAt),
            \sprintf('Product evaluation exists, it should not. Product uuid: %s', $productUuid->__toString())
        );
    }

    protected function isProductScoreComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): bool {
        return (bool) $this->get('database_connection')->executeQuery(
            <<<SQL
                SELECT product_uuid
                FROM pim_data_quality_insights_product_score
                WHERE product_uuid = :product_uuid AND evaluated_at = :evaluated_at
            SQL,
            ['product_uuid' => $productUuid->toBytes(), 'evaluated_at' => $evaluatedAt->format('Y-m-d')]
        )->fetchOne();
    }

    protected function assertProductModelScoreIsComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertTrue(
            $this->isProductModelScoreComputed($productModelId, $evaluatedAt),
            \sprintf('Product model evaluation does not exist. Product uuid: %s', $productModelId->__toString())
        );
    }

    protected function assertProductModelScoreIsNotComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertFalse(
            $this->isProductModelScoreComputed($productModelId, $evaluatedAt),
            \sprintf('Product model evaluation exists, it should not. Product model id: %s', $productModelId->__toString())
        );
    }

    protected function isProductModelScoreComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): bool {
        return (bool) $this->get('database_connection')->executeQuery(
            <<<SQL
                SELECT product_model_id
                FROM pim_data_quality_insights_product_model_score
                WHERE product_model_id = :product_model_id AND evaluated_at = :evaluated_at
            SQL,
            ['product_model_id' => $productModelId->toInt(), 'evaluated_at' => $evaluatedAt->format('Y-m-d')]
        )->fetchOne();
    }
}
