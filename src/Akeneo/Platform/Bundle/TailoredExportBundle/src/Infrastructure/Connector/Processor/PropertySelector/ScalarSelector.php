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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Platform\TailoredExport\Domain\SourceValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\StringValue;

/** TODO remove this selector when merging attribute selector and property selector */
class ScalarSelector implements PropertySelectorInterface
{
    public function applySelection(array $selectionConfiguration, SourceValue $sourceValue): string
    {
        return (string) $sourceValue->getData();
    }

    public function supports(array $selectionConfiguration, SourceValue $sourceValue): bool
    {
        return $sourceValue instanceof StringValue;
    }
}
