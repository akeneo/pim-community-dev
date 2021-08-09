<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindCategoryLabelsInterface
{
    /**
     * @param string[] $categoryCodes
     * @return array<string, string>
     */
    public function byCodes(array $categoryCodes, string $locale): array;
}
