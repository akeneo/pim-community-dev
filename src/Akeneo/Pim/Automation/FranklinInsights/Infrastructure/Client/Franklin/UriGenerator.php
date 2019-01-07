<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class UriGenerator
{
    /** @var string */
    protected $baseUri;

    /**
     * @param string $baseUri
     */
    public function __construct(string $baseUri)
    {
        $this->baseUri = rtrim($baseUri, '/');
    }

    /**
     * @param string $path
     * @param array $uriParameters
     * @param array $queryParameters
     *
     * @return string
     */
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

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @param array $uriParameters
     *
     * @return array
     */
    private function encodeUriParameters(array $uriParameters): array
    {
        return array_map(function ($uriParameter) {
            $uriParameter = rawurlencode($uriParameter);

            return preg_replace('~\%2F~', '/', $uriParameter);
        }, $uriParameters);
    }

    /**
     * @param array $queryParameters
     *
     * @return array
     */
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
