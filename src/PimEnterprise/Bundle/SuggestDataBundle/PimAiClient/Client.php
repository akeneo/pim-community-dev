<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient;

use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\ApiResponse;

interface Client
{
    public function getResource(string $uri, array $uriParameters): ApiResponse;

    public function createResource(string $uri, array $uriParameters = [], array $body = []): ApiResponse;
}
