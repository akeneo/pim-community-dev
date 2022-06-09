<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Tenant;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Firestore\FirestoreClient;

final class FirestoreContextFetcher implements TenantContextFetcherInterface
{
    private const TENANT_COLLECTION = 'tenant_contexts';

    public function __construct(
        private string $googleProjectId,
        private string $collection = self::TENANT_COLLECTION,
        private int $cacheTtl = 30
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function getTenantContext(string $tenantId): array
    {
        return \json_decode(json: $this->fetchContext($tenantId), associative: true, flags: \JSON_THROW_ON_ERROR);
    }

    /**
     * @throws GoogleException
     */
    private function fetchContext(string $tenantId): string
    {
        $cacheKey = \sprintf('%s.%s', $this->collection, $tenantId);
        $values = \apcu_fetch($cacheKey);

        if (false === $values) {
            $db = new FirestoreClient(
                [
                    'projectId' => $this->googleProjectId,
                ]
            );

            $docRef = $db->collection($this->collection)->document($tenantId);
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                throw new \RuntimeException(sprintf('Unable to fetch context for the "%s" tenant ID', $tenantId));
            }

            $values = $snapshot->data()['values'] ?? null;
            if (!\is_string($values)) {
                throw new \RuntimeException(sprintf('Unable to fetch context for the "%s" tenant ID', $tenantId));
            }
            \apcu_store($cacheKey, $values, $this->cacheTtl);
        }

        return $values;
    }
}
