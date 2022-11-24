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

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi;

final class LabelCollection
{
    /**
     * @param array<string, string> $labels
     */
    private function __construct(
        public readonly array $labels,
    ) {
    }

    /**
     * @param array<string, string> $labels
     */
    public static function fromArray(array $labels): self
    {
        return new self($labels);
    }

    public function getLabel(string $localeCode): ?string
    {
        return $this->labels[$localeCode] ?? null;
    }

    public function hasLabel(string $localeCode): bool
    {
        return array_key_exists($localeCode, $this->labels);
    }
}
