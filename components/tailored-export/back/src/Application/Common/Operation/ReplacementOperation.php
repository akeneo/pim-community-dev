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

namespace Akeneo\Platform\TailoredExport\Application\Common\Operation;

class ReplacementOperation implements OperationInterface
{
    public function __construct(private array $mapping)
    {
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function hasMappedValue(string $value): bool
    {
        return array_key_exists(strtolower($value), array_change_key_case($this->mapping));
    }

    public function getMappedValue(string $value): ?string
    {
        return array_change_key_case($this->mapping)[strtolower($value)] ?? null;
    }
}
