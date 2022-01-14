<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Application\Common;

interface MediaPathGeneratorInterface
{
    public function generate(
        string $identifier,
        string $attributeCode,
        ?string $scope,
        ?string $locale
    ): string;
}
