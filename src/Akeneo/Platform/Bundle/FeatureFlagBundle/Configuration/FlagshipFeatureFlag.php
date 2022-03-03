<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FlagshipFeatureFlag implements FeatureFlag
{
    private const CACHE_DURATION_IN_SECONDS = 3600;

    public function __construct(
        private HttpClientInterface $flagshipClient,
        private CacheInterface $cache,
        private string $visitorId,
        private string $flagName,
        private bool $defaultValue,
        private LoggerInterface $logger,
    ) {
    }

    public function isEnabled(): bool
    {
        try {
            $value = $this->cache->get($this->generateCacheKey(), function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_DURATION_IN_SECONDS);

                return $this->retrieveFlagValue();
            });
        } catch (ExceptionInterface $e) {
            $this->logger->error(sprintf('An error occured while calling Flagship : %s', $e->getMessage()));

            return $this->defaultValue;
        }

        return $value;
    }

    private function retrieveFlagValue(): bool
    {
        $response = $this->flagshipClient->request('POST', '', [
            'json' => [
                'visitor_id' => $this->visitorId,
            ],
        ]);

        $responseBody = $response->toArray();

        if (! array_key_exists('mergedModifications', $responseBody) || ! is_array($responseBody['mergedModifications'])) {
            throw new \Exception('Unexpected response from Flagship : key "mergedModifications" was not found');
        }

        if (!array_key_exists($this->flagName, $responseBody['mergedModifications']) || $responseBody['mergedModifications'][$this->flagName] === null) {
            return $this->defaultValue;
        }

        return $responseBody['mergedModifications'][$this->flagName];
    }

    private function generateCacheKey(): string
    {
        return $this->visitorId . '-' . $this->flagName;
    }
}
