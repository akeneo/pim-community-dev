<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Google\Cloud\PubSub\PubSubClient;

/**
 * Factory to create the Google PubSubClient.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PubSubClientFactory
{
    public function createPubSubClient(array $config): PubSubClient
    {
        return new PubSubClient($config);
    }
}
