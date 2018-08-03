<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi;

class UriGenerator
{
    protected $baseUri;

    public function __construct(string $baseUri)
    {
        $this->baseUri = rtrim($baseUri, '/');
    }

    public function generate(string $path, array $uriParameters = [], array $queryParameters = []): string
    {
        $uriParameters = $this->encodeUriParameters($uriParameters);

        $uri = $this->baseUri . '/' . vsprintf(ltrim($path, '/'), $uriParameters);

        $queryParameters = $this->booleanQueryParametersAsString($queryParameters);

        if (!empty($queryParameters)) {
            $uri .= '?' . http_build_query($queryParameters, null, '&', PHP_QUERY_RFC3986);
        }

        return $uri;
    }

    private function encodeUriParameters(array $uriParameters): array
    {
        return array_map(function ($uriParameter) {
            $uriParameter = rawurlencode($uriParameter);

            return preg_replace('~\%2F~', '/', $uriParameter);
        }, $uriParameters);
    }

    private function booleanQueryParametersAsString(array $queryParameters): array
    {
        return array_map(function ($queryParameters) {
            if (!is_bool($queryParameters)) {
                return $queryParameters;
            }

            return true === $queryParameters ? 'true' : 'false';
        }, $queryParameters);
    }
}
