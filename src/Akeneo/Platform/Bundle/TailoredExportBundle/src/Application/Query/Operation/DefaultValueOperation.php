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

class DefaultValueOperation implements OperationInterface
{
    private string $defaultValue;

    private function __construct(string $defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    public static function createFromNormalized(array $normalizedOperation): self
    {
        return new self((string) $normalizedOperation['value']);
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }
}
