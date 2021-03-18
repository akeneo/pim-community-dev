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
    /** @var ?string */
    private $keyFilePath = null;

    public function __construct(string $keyFilePath)
    {
        if (!empty($keyFilePath)) {
            $this->keyFilePath = $keyFilePath;
        }
    }

    public function createPubSubClient(array $config): PubSubClient
    {
        if (!empty($this->keyFilePath)) {
            return new PubSubClient(array_merge([
                'keyFilePath' => $this->keyFilePath
            ], $config));
        }
        return new PubSubClient($config);
    }
}
