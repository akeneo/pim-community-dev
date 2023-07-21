<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd;

use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class MessengerTestCase extends DataQualityInsightsTestCase
{
    private const MESSENGER_COMMAND_NAME = 'messenger:consume';

    private MessageBusInterface $productMessageBus;
    protected PubSubQueueStatus $productScoreComputeOnUpsertQueueStatus;

    protected function setUp(): void
    {
        \putenv('FLAG_DATA_QUALITY_INSIGHTS_UCS_EVENT_ENABLED=1');

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
}
