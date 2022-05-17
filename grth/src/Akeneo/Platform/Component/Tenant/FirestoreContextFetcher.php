<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Tenant;

use Google\Cloud\Firestore\FirestoreClient;

final class FirestoreContextFetcher implements TenantContextFetcherInterface
{
    private const TENANT_COLLECTION = 'tenant_contexts';

    public function __construct(
        private string $googleProjectId,
        private string $keyFilePath,
        private string $collection = self::TENANT_COLLECTION
    ) {
    }

    /**
     * @throws \Google\Cloud\Core\Exception\GoogleException
     * @throws \JsonException
     */
    public function getTenantContext(string $tenantId): array
    {
        $db = new FirestoreClient(
            [
                'projectId' => $this->googleProjectId,
                'keyFilePath' => $this->keyFilePath,
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

        return \json_decode(json: $values, associative: true, flags: \JSON_THROW_ON_ERROR);
    }
}
