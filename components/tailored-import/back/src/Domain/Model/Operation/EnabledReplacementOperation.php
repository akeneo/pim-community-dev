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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

use Webmozart\Assert\Assert;

final class EnabledReplacementOperation implements OperationInterface
{
    public const TYPE = 'enabled_replacement';

    public function __construct(
        private string $uuid,
        private array $mapping,
    ) {
        Assert::uuid($uuid);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function normalize(): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => self::TYPE,
            'mapping' => $this->mapping,
        ];
    }

    public function hasMappedValue(string $value): bool
    {
        return in_array($value, $this->mapping['true']) || in_array($value, $this->mapping['false']);
    }

    public function getMappedValue(string $value): bool
    {
        return match (true) {
            in_array($value, $this->mapping['true']) => true,
            in_array($value, $this->mapping['false']) => false,
            default => throw new \InvalidArgumentException(sprintf('Value "%s" is not mapped', $value)),
        };
    }
}
