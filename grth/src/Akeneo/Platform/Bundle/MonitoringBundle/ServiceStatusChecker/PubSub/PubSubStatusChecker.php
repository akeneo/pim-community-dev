<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\PubSub;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\Core\Exception\ServiceException;
use Psr\Log\LoggerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PubSubStatusChecker implements PubSubStatusCheckerInterface
{
    private PubSubClientFactory $pubSubClientFactory;
    private string $projectId;
    private string $topicName;
    private string $subscriptionName;
    private LoggerInterface $logger;

    public function __construct(
        PubSubClientFactory $pubSubClientFactory,
        string $projectId,
        string $topicName,
        string $subscriptionName,
        LoggerInterface $logger
    ) {
        $this->pubSubClientFactory = $pubSubClientFactory;
        $this->projectId = $projectId;
        $this->topicName = $topicName;
        $this->subscriptionName = $subscriptionName;
        $this->logger = $logger;
    }

    public function status(): ServiceStatus
    {
        try {
            $pubSubClient = $this->pubSubClientFactory->createPubSubClient(['projectId' => $this->projectId]);
            $topic = $pubSubClient->topic($this->topicName);
            $subscription = $topic->subscription($this->subscriptionName);
            $messages = $subscription->pull([
                'maxMessages' => 1,
                'returnImmediately' => true,
            ]);

            if (count($messages) > 0) {
                $subscription->modifyAckDeadline($messages[0], 0);
            }
        } catch (\Throwable $exception) {
            $this->logger->error("PubSub ServiceCheck error", ['exception' => $exception]);
            return ServiceStatus::notOk(sprintf('Unable to access Pub/Sub: %s', $exception->getMessage()));
        }

        return ServiceStatus::ok();
    }
}
