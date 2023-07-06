<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Psr\Cache\CacheItemPoolInterface;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Factory to create the Google PubSubClient.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PubSubClientFactory
{
    private array $baseConfig = [];

    public function __construct(string $keyFilePath = null, CacheItemPoolInterface $authCache = null)
    {
        if (!empty($keyFilePath)) {
            $this->baseConfig['keyFilePath'] = $keyFilePath;
        }
        if (!is_null($authCache)) {
            $this->baseConfig['authCache'] = $authCache;
        }
    }

    public function createPubSubClient(array $config): PubSubClient
    {
        return new PubSubClient(array_merge($this->baseConfig, $config));
    }
}
