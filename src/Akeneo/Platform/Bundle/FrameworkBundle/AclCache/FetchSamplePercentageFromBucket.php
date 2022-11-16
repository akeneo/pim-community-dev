<?php

namespace Akeneo\Platform\Bundle\FrameworkBundle\AclCache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\FlushableCache;
use Doctrine\Common\Cache\MultiOperationCache;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class FetchSamplePercentageFromBucket implements FetchSamplePercentage
{
    private const BUCKET_CONFIG = 'https://storage.googleapis.com/ake-memcache-test/value_7f0df927-5a23-4d62-8f20-50cfc2189f59.json';
    private Client $client;

    public function __construct(
        private readonly string $googleProject
    ) {
        $this->client = new Client([
            'timeout' => 2,
            'http_errors' => false
        ]);
    }

    public function fetch(): int
    {
        $response = $this->client->get(self::BUCKET_CONFIG);
        if ($response->getStatusCode() !== 200) {
            return 0;
        }

        $rawConfig = $response->getBody()->getContents();
        $config = \json_decode($rawConfig, true);

        $explodedGoogleProject = explode('-', $this->googleProject);
        $env = end($explodedGoogleProject);
        if (empty($env)) {
            return 0;
        }

        if (!is_array($config) && !isset($config[$env])) {
            return 0;
        }

        $samplePercentage = $config[$env];
        if (!is_int($samplePercentage)) {
            return 0;
        }

        if ($samplePercentage >= 0 && $samplePercentage <= 100) {
            return $samplePercentage;
        } else {
            return 0;
        }
    }
}
