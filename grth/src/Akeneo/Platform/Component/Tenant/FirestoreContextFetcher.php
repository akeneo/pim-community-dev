<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Tenant;

use Akeneo\Platform\Component\Tenant\Exception\TenantContextInvalidFormatException;
use Akeneo\Platform\Component\Tenant\Exception\TenantContextNotFoundException;
use Akeneo\Platform\Component\Tenant\Exception\TenantContextNotReadyException;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Firestore\FirestoreClient;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

final class FirestoreContextFetcher implements TenantContextFetcherInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TenantContextDecoderInterface $tenantContextDecoder,
        private readonly string $googleProjectId,
        private readonly string $collection,
        private readonly int $cacheTtl = 30
    ) {
        Assert::notEmpty($googleProjectId, 'The Google Project ID must not be empty');
        Assert::notEmpty($collection, 'The collection name must not be empty');
    }

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
        $contextValues = \apcu_fetch($cacheKey);

        if (false === $contextValues) {
            $db = new FirestoreClient(
                [
                    'projectId' => $this->googleProjectId,
                ]
            );

            $docRef = $db->collection($this->collection)->document($tenantId);
            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                throw new TenantContextNotFoundException(
                    sprintf('Unable to fetch context for the "%s" tenant ID: the document does not exist', $tenantId)
                );
            }

            $tenantDocument = $snapshot->data();
            if (!$this->isTenantDataValid($tenantDocument)) {
                $this->logger->info(
                    'Tenant store document for tenant ID "%s"',
                    ['document' => $tenantDocument]
                );
                throw new TenantContextInvalidFormatException(
                    sprintf(
                        'Unable to fetch context for the "%s" tenant ID: missing key in the document.',
                        $tenantId
                    )
                );
            }

            if (!$this->isTenantReady($tenantDocument)) {
                $this->logger->info(
                    'Tenant store document for tenant ID "%s"',
                    ['document' => $tenantDocument]
                );
                throw new TenantContextNotReadyException(
                    sprintf(
                        'Context not available for "%s" tenant ID. Status = %s',
                        $tenantId,
                        $tenantDocument['status']
                    )
                );
            }

            $contextValues = $tenantDocument['context'] ?? null;
            $this->logger->debug(
                \sprintf(
                    'Context for %s tenant fetched from Firestore in %.4Fs',
                    $tenantId,
                    \microtime(true) - $start
                )
            );
            \apcu_store($cacheKey, $contextValues, $this->cacheTtl);
        }

        $this->logger->debug(
            \sprintf(
                'Context for %s tenant fetched in %.6Fs',
                $tenantId,
                \microtime(true) - $start
            )
        );

        return $contextValues;
    }

    private function isTenantDataValid(array $tenantData): bool
    {
        $tenantStatus = $tenantData['status'] ?? null;
        $tenantContext = $tenantData['context'] ?? null;

        return (null !== $tenantStatus) && (null !== $tenantContext);
    }

    private function isTenantReady(array $tenantData): bool
    {
        $tenantStatus = $tenantData['status'] ?? null;

        return TenantContextStatuses::TENANT_STATUS_CREATED->value === $tenantStatus;
    }
}
