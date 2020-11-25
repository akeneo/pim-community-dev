<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\PubSub;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\Core\Exception\ServiceException;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PubSubStatusChecker implements PubSubStatusCheckerInterface
{
    private PubSubClientFactory $pubSubClientFactory;
    private string $project;
    private string $topicName;
    private string $subscriptionName;

    public function __construct(
        PubSubClientFactory $pubSubClientFactory,
        string $project,
        string $topicName,
        string $subscriptionName
    ) {
        $this->pubSubClientFactory = $pubSubClientFactory;
        $this->project = $project;
        $this->topicName = $topicName;
        $this->subscriptionName = $subscriptionName;
    }

    public function status(): ServiceStatus
    {
        $pubSubClient = $this->pubSubClientFactory->createPubSubClient(['projectId' => $this->project]);

        $topic = $pubSubClient->topic($this->topicName);
        $subscription = $topic->subscription($this->subscriptionName);

        try {
            $messages = $subscription->pull([
                'maxMessages' => 1,
                'returnImmediately' => true,
            ]);

            if (count($messages) > 0) {
                $subscription->modifyAckDeadline($messages[0], 0);
            }
        } catch (ServiceException $exception) {
            return ServiceStatus::notOk('Unable to access Pub/Sub.');
        }

        return ServiceStatus::ok();
    }
}
