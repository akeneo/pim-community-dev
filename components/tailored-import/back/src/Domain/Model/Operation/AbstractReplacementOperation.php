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

abstract class AbstractReplacementOperation implements OperationInterface
{
    public function __construct(
        protected string $uuid,
        protected array $mapping,
    ) {
        Assert::uuid($uuid);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getMappedValue(string $value): string|null
    {
        $mapping = [];
        foreach ($this->getMapping() as $key => $values) {
            $mapping[] = array_fill_keys(array_values($values), $key);
        }

        if (empty($mapping)) {
            return null;
        }

        $mapping = array_replace(...$mapping);

        if (!array_key_exists($value, $mapping)) {
            return null;
        }

        return (string) $mapping[$value];
    }
}
