<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindAttributeOptionLabelsInterface
{
    public function byAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes, string $locale): array;
}
