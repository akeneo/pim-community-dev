<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector;

class MediaExporterPathGenerator
{
    public static function generate(string $identifier, string $attributeCode, ?string $scope, ?string $locale): string
    {
        $identifier = str_replace(DIRECTORY_SEPARATOR, '_', $identifier);
        $target = sprintf('files/%s/%s', $identifier, $attributeCode);

        if (null !== $locale) {
            $target .= DIRECTORY_SEPARATOR . $locale;
        }
        if (null !== $scope) {
            $target .= DIRECTORY_SEPARATOR . $scope;
        }

        return $target . DIRECTORY_SEPARATOR;
    }
}
