<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd;

use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class MessengerTestCase extends DataQualityInsightsTestCase
{
    private const MESSENGER_COMMAND_NAME = 'messenger:consume';

    /** @var PubSubQueueStatus */
    protected array $pubSubQueueStatuses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushQueues();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->flushQueues();
    }

    protected function flushQueues(): void
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
}
