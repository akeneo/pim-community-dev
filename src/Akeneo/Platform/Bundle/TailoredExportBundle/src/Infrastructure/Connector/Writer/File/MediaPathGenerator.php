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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File;

use Akeneo\Platform\TailoredExport\Application\Common\MediaPathGeneratorInterface;

class MediaPathGenerator implements MediaPathGeneratorInterface
{
    public function generate(string $identifier, string $attributeCode, ?string $scope, ?string $locale): string
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
