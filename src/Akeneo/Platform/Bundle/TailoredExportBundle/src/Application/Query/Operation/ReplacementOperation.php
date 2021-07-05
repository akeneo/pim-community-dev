<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Query\Operation;

class ReplacementOperation implements OperationInterface
{
    private array $mapping;

    private function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public static function createFromNormalized(array $normalizedOperation): self
    {
        return new self($normalizedOperation['mapping']);
    }

    public function hasMappedValue(string $value): bool
    {
        return array_key_exists($value, $this->mapping);
    }

    public function getMappedValue(string $value): ?string
    {
        /** check after if we return null */
        if (!$this->hasMappedValue($value)) {
            return null;
        }

        return $this->mapping[$value];
    }
}
