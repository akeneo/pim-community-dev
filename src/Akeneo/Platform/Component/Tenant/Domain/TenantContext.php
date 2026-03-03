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
use Akeneo\Platform\Component\Tenant\Domain\Values\EncryptedValues;

/**
 * TenantContext contains all context values for one tenant
 *
 * TODO: $v1Values comes from the first context format. Kept for BC and will be removed in next versions
 *
 * @author  JM Leroux <jmleroux.pro@gmail.com>
 */
final class TenantContext
{
    private function __construct(
        private readonly ContextValueDecrypterInterface $tenantContextDecoder,
        private readonly ?EncryptedValues $v1Values = null,
        private readonly ?array $plainValues = null,
        private readonly ?EncryptedValues $secretValues = null,
    ) {
    }

    public function parseContextValues(): array
    {
        if ($this->plainValues !== null && $this->secretValues !== null) {
            return array_merge(
                $this->secretValues->decode($this->tenantContextDecoder),
                $this->plainValues,
            );
        }

        return $this->v1Values->decode($this->tenantContextDecoder);
    }

    public static function createFromContextStore(
        ContextValueDecrypterInterface $tenantContextDecoder,
        array $context,
    ): self {
        if (array_diff(array_keys($context), ['v1_values', 'plain_values', 'secret_values'])
        ) {
            throw new TenantContextInvalidFormatException('Invalid values in context');
        }

        $v1Values = isset($context['v1_values']) ? EncryptedValues::create($context['v1_values']) : null;
        $plainValues = $context['plain_values'] ?? null;
        $secretValues = isset($context['secret_values']) ? EncryptedValues::create($context['secret_values']) : null;

        return new self(
            $tenantContextDecoder,
            $v1Values,
            $plainValues,
            $secretValues,
        );
    }

    public static function createFromCache(
        ContextValueDecrypterInterface $tenantContextDecoder,
        array $cachedValues
    ): self {
        return self::createFromContextStore($tenantContextDecoder, $cachedValues);
    }

    public function cacheValues(string $cacheKey, int $cacheTtl): void
    {
        \apcu_store(
            $cacheKey,
            [
                'v1_values' => $this->v1Values?->normalize(),
                'plain_values' => $this->plainValues,
                'secret_values' => $this->secretValues?->normalize(),
            ],
            $cacheTtl
        );
    }
}
