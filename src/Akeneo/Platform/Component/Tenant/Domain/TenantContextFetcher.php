<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Tenant\Domain;

use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextInvalidFormatException;
use Akeneo\Platform\Component\Tenant\Domain\Exception\TenantContextNotReadyException;
use Psr\Log\LoggerInterface;

/**
 * @author  JM Leroux <jmleroux.pro@gmail.com>
 */
final class TenantContextFetcher implements TenantContextFetcherInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ContextValueDecrypterInterface $tenantContextDecoder,
        private readonly int $cacheTtl = 30
    ) {
    }

    public function getTenantContext(string $tenantId, ContextStoreInterface $contextStore): array
    {
        try {
            $contextValues = $this->fetchContext($tenantId, $contextStore);

            return $contextValues->parseContextValues();
        } catch (\Throwable $e) {
            $this->logger->critical(
                \sprintf('Context could not be fetched for %s tenant: %s', $tenantId, $e->getMessage()),
                ['exception' => $e]
            );
            throw $e;
        }
    }

    private function fetchContext(
        string $tenantId,
        ContextStoreInterface $contextStore,
    ): TenantContext {
        $start = \microtime(true);
        $cacheKey = \sprintf('tenant_context.%s', $tenantId);
        $cachedValues = \apcu_fetch($cacheKey);

        if (false === $cachedValues) {
            $tenantDocument = $contextStore->findDocumentById($tenantId);

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

            $context = $tenantDocument['context'];

            // First context store format: all values encrypted in a stringified JSON
            if (is_string($context)) {
                $context = [
                    'v1_values' => \json_decode($context, true),
                ];
            }

            $values = TenantContext::createFromContextStore(
                $this->tenantContextDecoder,
                $context,
            );
            $values->cacheValues($cacheKey, $this->cacheTtl);

            $this->logger->debug(
                \sprintf(
                    'Context for %s tenant fetched from Firestore in %.4Fs',
                    $tenantId,
                    \microtime(true) - $start
                )
            );
        } else {
            $values = TenantContext::createFromCache($this->tenantContextDecoder, $cachedValues);
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

    private function isTenantDataValid(array $tenantData): bool
    {
        $tenantStatus = $tenantData['status'] ?? null;

        if (null === $tenantStatus) {
            return false;
        }

        if (!isset($tenantData['context']) || null === $tenantData['context']) {
            return false;
        }

        if (
            is_array($tenantData['context'])
            && (!isset($tenantData['context']['plain_values']) || !isset($tenantData['context']['secret_values']))
        ) {
            return false;
        }

        return true;
    }

    private function isTenantReady(array $tenantData): bool
    {
        $tenantStatus = $tenantData['status'] ?? null;

        return in_array(
            $tenantStatus,
            [
                TenantContextStatuses::TENANT_STATUS_CREATED->value,
                TenantContextStatuses::TENANT_STATUS_CREATION_IN_PROGRESS->value,
            ]
        );
    }
}
