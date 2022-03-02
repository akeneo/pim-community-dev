<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FlagshipFeatureFlag implements FeatureFlag
{
    private HttpClientInterface $flagshipClient;
    private string $flagName;
    private bool $defaultValue;
    private string $visitorId;

    public function __construct(HttpClientInterface $flagshipClient, string $visitorId, string $flagName, bool $defaultValue)
    {
        $this->flagshipClient = $flagshipClient;
        $this->visitorId = $visitorId;
        $this->flagName = $flagName;
        $this->defaultValue = $defaultValue;
    }

    public function isEnabled(): bool
    {
        try {
            $response = $this->flagshipClient->request('POST', '', [
                'json' => [
                    'visitor_id' => $this->visitorId,
                ],
            ]);

            $responseBody = $response->toArray();
        } catch (\Exception $e) {
            //Log errors
            dump($e->getMessage());
        }

        if (!array_key_exists('mergedModifications', $responseBody) || !is_array($responseBody['mergedModifications'])) {
            throw new \Exception('Unexpected response from Flagship : key "mergedModifications" was not found');
        }

        if (array_key_exists($this->flagName, $responseBody['mergedModifications'])) {
            //The flagvalue can be null or a boolean value
            return boolval($responseBody['mergedModifications'][$this->flagName]);
        }

        return $this->defaultValue;
    }
}
