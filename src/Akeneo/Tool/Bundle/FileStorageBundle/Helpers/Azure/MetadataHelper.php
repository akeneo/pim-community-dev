<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Helpers\Azure;

final class MetadataHelper
{
    /**
     * @param string[][] $headers
     * @return array<string, string>
     */
    public static function headersToMetadata(array $headers): array
    {
        $metadata = [];

        foreach ($headers as $key => $value) {
            if (str_starts_with($key, "x-ms-meta-")) {
                $metadata[substr($key, strlen("x-ms-meta-"))] = implode(', ', $value);
            }
        }

        return $metadata;
    }
}
