<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Middleware;

use Akeneo\Tool\Bundle\FileStorageBundle\Auth\StorageSharedKeyCredential;
use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\RequestInterface;

final class AddAuthorizationHeaderMiddleware
{
    private const INCLUDED_HEADERS = [
        'Content-Encoding',
        'Content-Language',
        'Content-Length',
        'Content-MD5',
        'Content-Type',
        'Date',
        'If-Modified-Since',
        'If-Match',
        'If-None-Match',
        'If-Unmodified-Since',
        'Range',
    ];

    public function __construct(private readonly StorageSharedKeyCredential $sharedKeyCredential)
    {
    }

    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $accountName = $this->sharedKeyCredential->accountName;
            $stringToSign = $this->computeStringToSign($request);
            $signature = $this->sharedKeyCredential->computeHMACSHA256($stringToSign);

            $request = $request->withHeader('Authorization', sprintf('SharedKey %s:%s', $accountName, $signature));

            return $handler($request, $options);
        };
    }

    private function computeStringToSign(RequestInterface $request): string
    {
        $verb = strtoupper($request->getMethod());

        $headers = array_map(fn ($value) => implode(', ', $value), $request->getHeaders());
        if (isset($headers['Content-Length']) && $headers['Content-Length'] === '0') {
            $headers['Content-Length'] = '';
        }

        $query = Query::parse($request->getUri()->getQuery());
        $url = (string) $request->getUri();

        $stringToSign = [$verb];

        foreach (self::INCLUDED_HEADERS as $header) {
            $stringToSign[] = array_change_key_case($headers)[strtolower($header)] ?? null;
        }

        $stringToSign[] = $this->computeCanonicalizedHeaders($headers);
        $stringToSign[] = $this->computeCanonicalizedResource($url, $query);

        return implode("\n", $stringToSign);
    }

    /**
     * @param array<string, string> $headers
     */
    private function computeCanonicalizedHeaders(array $headers): string
    {
        $normalizedHeaders = [];

        foreach ($headers as $header => $value) {
            // Convert header to lower case.
            $header = strtolower($header);

            // Retrieve all headers for the resource that begin with x-ms-,
            // including the x-ms-date header.
            if (str_starts_with($header, 'x-ms-')) {
                // Unfold the string by replacing any breaking white space
                // (meaning what splits the headers, which is \r\n) with a single
                // space.
                $value = str_replace("\r\n", ' ', $value);

                // Trim any white space around the colon in the header.
                $value = ltrim($value);
                $header = rtrim($header);

                $normalizedHeaders[$header] = $value;
            }
        }

        // Sort the headers lexicographically by header name, in ascending order.
        // Note that each header may appear only once in the string.
        ksort($normalizedHeaders);

        $canonicalizedHeaders = [];
        foreach ($normalizedHeaders as $key => $value) {
            $canonicalizedHeaders[] = $key . ':' . $value;
        }

        return implode("\n", $canonicalizedHeaders);
    }

    /**
     * @param string $url
     * @param array<string, string> $queryParams
     * @return string
     */
    private function computeCanonicalizedResource(string $url, array $queryParams): string
    {
        $queryParams = array_change_key_case($queryParams);

        // 1. Beginning with an empty string (""), append a forward slash (/),
        //    followed by the name of the account that owns the accessed resource.
        $canonicalizedResource = '/' . $this->sharedKeyCredential->accountName;

        // 2. Append the resource's encoded URI path, without any query parameters.
        $canonicalizedResource .= parse_url($url, PHP_URL_PATH);

        // 3. Retrieve all query parameters on the resource URI, including the comp
        //    parameter if it exists.
        // 4. Sort the query parameters lexicographically by parameter name, in
        //    ascending order.
        ksort($queryParams);

        // 5. Convert all parameter names to lowercase.
        // 6. URL-decode each query parameter name and value.
        // 7. Append each query parameter name and value to the string in the
        //    following format:
        //      parameter-name:parameter-value
        // 9. Group query parameters
        // 10. Append a new line character (\n) after each name-value pair.
        foreach ($queryParams as $key => $value) {
            // $value must already be ordered lexicographically
            // See: ServiceRestProxy::groupQueryValues
            $canonicalizedResource .= "\n" . $key . ':' . $value;
        }

        return $canonicalizedResource;
    }
}
