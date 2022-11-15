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
    private const BUCKET_CONFIG = 'https://storage.googleapis.com/ake-memcache-test/value_TcfmHa.json';
    private Client $client;

    public function __construct(
        private readonly string $googleProject
    ){
        $this->client = new Client([
            'timeout' => 2,
            'headers' => ['Authorization' => null],
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

        if (!is_array($config) && !isset($config[$this->googleProject])) {
            return 0;
        }

        $samplePercentage = $config[$this->googleProject];
        if (!is_int($samplePercentage)) {

            return 0;
        }

        if ($samplePercentage >= 0 && $samplePercentage <= 100) {
            return $samplePercentage;
        } else {
            return 0;
        }
    }

    private function getSamplePercentageFromApcu(): int
    {
        $samplePercentage = \apcu_fetch(self::APCU_KEY);
        if (!is_int($samplePercentage)) {
            $samplePercentageFromBucket = $this->getSamplePercentageFromBucket();
            \apcu_store(self::APCU_KEY, $samplePercentageFromBucket, 300);

            return $samplePercentageFromBucket;
        }

        return $samplePercentage;
    }
}
