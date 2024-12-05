<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Helpers\Azure;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

final class ConnectionStringHelper
{
    private const DEV_CONNECTION_STRING_SHORTCUT = 'UseDevelopmentStorage=true';
    private const DEV_BLOB_ENDPOINT = 'http://127.0.0.1:10000/devstoreaccount1';
    private const DEV_BLOB_ACCOUNT_NAME = "devstoreaccount1";
    private const DEV_BLOB_ACCOUNT_KEY = "Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==";

    public static function getBlobEndpoint(string $connectionString): ?UriInterface
    {
        if($connectionString === self::DEV_CONNECTION_STRING_SHORTCUT) {
            return new Uri(self::DEV_BLOB_ENDPOINT);
        }

        $segments = self::getSegments($connectionString);

        if (isset($segments['BlobEndpoint'])) {
            $uri = $segments['BlobEndpoint'];
        } elseif(isset($segments['AccountName'], $segments['EndpointSuffix'])) {
            $uri = sprintf('%s.blob.%s', $segments['AccountName'], $segments['EndpointSuffix']);
        } else {
            return null;
        }

        $uriWithoutScheme = preg_replace("(^https?://)", "", $uri);
        $scheme = $segments['DefaultEndpointsProtocol'] ?? "https";

        return new Uri("$scheme://$uriWithoutScheme");
    }

    public static function getAccountName(string $connectionString): ?string
    {
        if($connectionString === self::DEV_CONNECTION_STRING_SHORTCUT) {
            return self::DEV_BLOB_ACCOUNT_NAME;
        }

        return self::getSegments($connectionString)['AccountName'] ?? null;
    }

    public static function getAccountKey(string $connectionString): ?string
    {
        if($connectionString === self::DEV_CONNECTION_STRING_SHORTCUT) {
            return self::DEV_BLOB_ACCOUNT_KEY;
        }

        return self::getSegments($connectionString)['AccountKey'] ?? null;
    }

    public static function getSas(string $connectionString): ?string
    {
        return self::getSegments($connectionString)['SharedAccessSignature'] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private static function getSegments(string $connectionString): array
    {
        $segments = [];
        foreach (explode(';', $connectionString) as $segment) {
            if ($segment !== "") {
                [$key, $value] = explode('=', $segment, 2);
                $segments[$key] = $value;
            }
        }

        return $segments;
    }
}
