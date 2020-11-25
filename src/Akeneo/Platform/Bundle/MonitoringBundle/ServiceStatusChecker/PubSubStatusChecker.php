<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PubSubStatusChecker implements StatusChecker
{
    private PubSubClientFactory $pubSubClientFactory;
    private string $project;
    private string $topicName;

    public function __construct(PubSubClientFactory $pubSubClientFactory, string $project, string $topicName)
    {
        $this->pubSubClientFactory = $pubSubClientFactory;
        $this->project = $project;
        $this->topicName = $topicName;
    }

    public function status(): ServiceStatus
    {
        $pubSubClient = $this->pubSubClientFactory->createPubSubClient(['projectId' => $this->project]);

        return $pubSubClient->topic($this->topicName)->exists() ? ServiceStatus::ok() : ServiceStatus::notOk(
            'Unable to access Pub/Sub.'
        );
    }
}
