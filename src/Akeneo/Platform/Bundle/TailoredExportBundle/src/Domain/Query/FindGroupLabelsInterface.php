<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindGroupLabelsInterface
{
    /**
     * @param string[] $groupCodes
     * @return array<string, string>
     */
    public function byCodes(array $groupCodes, string $locale): array;
}
