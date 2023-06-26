<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class MessengerTestCase extends TestCase
{
    private const MESSENGER_COMMAND_NAME = 'messenger:consume';

    private MessageBusInterface $productMessageBus;
    protected PubSubQueueStatus $productScoreComputeOnUpsertQueueStatus;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');

        $this->productMessageBus = $this->get('pim_enrich.product.message_bus');
        $this->productScoreComputeOnUpsertQueueStatus = $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_product_score_compute_on_upsert');

        // Be sure the subscription is created before any tests
        $subscription = $this->productScoreComputeOnUpsertQueueStatus->getSubscription();
        if (!$subscription->exists()) {
            $subscription->create();
        }

        $this->productScoreComputeOnUpsertQueueStatus->flushJobQueue();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->productScoreComputeOnUpsertQueueStatus->flushJobQueue();
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

        self::assertSame(0, $process->getExitCode(), 'An error occurred: ' . $process->getErrorOutput());
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

    protected function createOrUpdateProduct(UuidInterface $uuid): void
    {
        $command = UpsertProductCommand::createWithUuid(
            $this->getUserId('admin'),
            \Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid::fromUuid($uuid),
            [new SetIdentifierValue('sku', (Uuid::uuid4())->toString())]
        );
        $this->productMessageBus->dispatch($command);
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    protected function simulateOldProductScoreCompute(): void
    {
        $this->get('database_connection')->executeQuery(
            'UPDATE pim_data_quality_insights_product_score SET evaluated_at = "1980-01-01"'
        );
    }
}
