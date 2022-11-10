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

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Firestore\FirestoreClient;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

final class FirestoreContextFetcher implements TenantContextFetcherInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private TenantContextDecoderInterface $tenantContextDecoder,
        private string $googleProjectId,
        private string $collection,
        private int $cacheTtl = 30
    ) {
        Assert::notEmpty($googleProjectId, 'The Google Project ID must not be empty');
        Assert::notEmpty($collection, 'The collection name must not be empty');
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
                throw new \RuntimeException(
                    sprintf('Unable to fetch context for the "%s" tenant ID: the document does not exist', $tenantId)
                );
            }

            $contextValues = $snapshot->data()['context'] ?? null;
            if (!\is_string($contextValues)) {
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
}
