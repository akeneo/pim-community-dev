<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

class PhpMemoryLimit
{
    const MAXIMUM_MEMORY_IN_BYTES = 1024*1024*1024*1024; // 512TB
    const DEFAULT_MEMORY_IN_BYTES_WHEN_NOT_SET = 128*1024*1024; // 128MB

    public function asBytesFromPHPConfig(): int
    {
        $limit = ini_get('memory_limit');

        return $this->asBytes($limit);

    }

    public function asBytes(string $limit): int
    {
        if (!is_string($limit)) {
            return self::DEFAULT_MEMORY_IN_BYTES_WHEN_NOT_SET;
        }

        $lastCharacter = substr($limit, -1);
        if ($lastCharacter === 'K') {
            return  (int) substr($limit, 0, -1) * 1024;
        }
        if ($lastCharacter === 'M') {
            return  (int) substr($limit, 0, -1) * 1024 * 1024;
        }
        if ($lastCharacter === 'G') {
            return  (int) substr($limit, 0, -1) * 1024 * 1024 * 1024;
        }

        if ((int) $limit === -1 || '' === trim($limit)) {
            return self::MAXIMUM_MEMORY_IN_BYTES;
        }

        if ((int) $limit < -1 || (int) $limit === 0) {
            return self::DEFAULT_MEMORY_IN_BYTES_WHEN_NOT_SET;
        }


        return (int) $limit;
    }

}
