<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Tenant;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Firestore\FirestoreClient;
use Psr\Log\LoggerInterface;

final class FirestoreContextFetcher implements TenantContextFetcherInterface
{
    private const TENANT_COLLECTION = 'tenant_contexts';

    public function __construct(
        private LoggerInterface $logger,
        private TenantContextDecoderInterface $tenantContextDecoder,
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
        try {
            $contextValues = $this->fetchContext($tenantId);

            return $this->tenantContextDecoder->decode($contextValues);
        } catch (\Throwable $e) {
            $this->logger->critical(
                \sprintf('Context could not be fetched for %s tenant', $tenantId),
                ['exception' => $e]
            );
            throw $e;
        }
    }

    /**
     * @throws GoogleException
     */
    private function fetchContext(string $tenantId): string
    {
        $start = \microtime(true);
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
                throw new \RuntimeException(
                    sprintf('Unable to fetch context for the "%s" tenant ID: the document does not exist', $tenantId)
                );
            }

            $values = $snapshot->data()['values'] ?? null;
            if (!\is_string($values)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to fetch context for the "%s" tenant ID: the document has an incorrect format',
                        $tenantId
                    )
                );
            }
            $this->logger->debug(
                \sprintf(
                    'Context for %s tenant fetched from Firestore in %.4Fs',
                    $tenantId,
                    \microtime(true) - $start
                )
            );
            \apcu_store($cacheKey, $values, $this->cacheTtl);
        }

        $this->logger->debug(
            \sprintf(
                'Context for %s tenant fetched in %.6Fs',
                $tenantId,
                \microtime(true) - $start
            )
        );

        return $values;
    }
}
