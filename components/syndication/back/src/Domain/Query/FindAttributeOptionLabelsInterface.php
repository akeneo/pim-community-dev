<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Domain\Query;

interface FindAttributeOptionLabelsInterface
{
    /**
     * @param string[] $optionCodes
     * @return array<string, string>
     */
    public function byAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes, string $locale): array;
}
