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

final class MultiSelectReplacementOperation implements OperationInterface
{
    public const TYPE = 'multi_select_replacement';

    public function __construct(
        private string $uuid,
        private array $mapping,
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function normalize(): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => self::TYPE,
            'mapping' => $this->mapping,
        ];
    }

    public function getMappedValue(string $value): string|null
    {
        $mapping = [];
        foreach ($this->getMapping() as $key => $values) {
            $mapping[] = array_fill_keys(array_values($values), $key);
        }

        $mapping = array_merge(...$mapping);

        if (!array_key_exists($value, $mapping)) {
            return null;
        }

        return (string) $mapping[$value];
    }
}
